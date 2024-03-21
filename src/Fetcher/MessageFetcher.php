<?php

namespace App\Fetcher;

use App\Fetcher\MessageFetcherInterface;
use App\Traits\EntityManagerTrait;
use App\Repository\MessageRepository;
use SecIT\ImapBundle\Connection\ConnectionInterface;

class MessageFetcher implements MessageFetcherInterface
{
    use EntityManagerTrait;

	public function __construct(
        private MessageRepository $messageRepository,
        private ConnectionInterface $appInboxConnection,
    ) {}

    public function fetch(): void
    {
    	$mailbox = $this->appInboxConnection->getMailbox();
        $mailIds = $mailbox->searchMailbox('ALL');

        $excludedMailIds = $this->messageRepository->findEmailIds();
        $mailIds = array_filter($mailIds, fn($mi) => !in_array($mi, $excludedMailIds));

        $mailsSaved = 0;
        foreach ($mailIds as $mailId) {
            $emailContent = $mailbox->getMail($mailId)->textHtml;
            $template = "/^\<strong\>Nadawca\:\<\/strong\> (?P<sender>\d+) " . 
                "\<strong\>Odbiorca\:\<\/strong\> (?P<receiver>\d+)\<br\>\r\n" .
                "\<strong\>Treść odebranej wiadomości\:\<\/strong\> (?P<content>.+)\<br\>\r\n" . 
                "\<strong\>Data\:\<\/strong\> (?P<received_at>[\d\-]+\s[\d\:]+)\<br\>$/uUs";
            $matches = [];

            if (preg_match($template, $emailContent, $matches)) {
                $message = (new Message())
                    ->setSender($matches['sender'])
                    ->setReceiver($matches['receiver'])
                    ->setContent($matches['content'])
                    ->setReceivedAt(new \DateTimeImmutable($matches['received_at']))
                    ->setEmailId($mailId)
                ;
                $this->entityManager->persist($message);
                $mailsSaved++;

                if ($mailsSaved > 100) {
                    $this->entityManager->flush();
                }
            }
        }

        $this->entityManager->flush();
    }
}