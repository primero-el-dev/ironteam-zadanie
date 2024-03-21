<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Traits\EntityManagerTrait;
use App\Repository\MessageRepository;
use App\Fetcher\MessageFetcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/message')]
class MessageController extends AbstractController
{
    use EntityManagerTrait;

    public function __construct(
        private MessageRepository $messageRepository,
        private MessageFetcherInterface $messageFetcher,
    ) {}

    #[Route('/', name: 'app_message_index')]
    public function index(): Response
    {
        return $this->render('message/index.html.twig', [
            'messages' => $this->messageRepository->findAll(),
        ]);
    }

    #[Route('/create', name: 'app_message_create')]
    public function create(Request $request): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $this->addFlash('success', 'New message was added successfully.');

            return $this->redirectToRoute('app_message_index');
        }

        return $this->render('message/create.html.twig', [
            'messageForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_message_edit')]
    public function edit(
        Request $request, 
        #[MapEntity(id: 'id')] Message $message
    ): Response {
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $this->addFlash('success', 'Message was edited successfully.');

            return $this->redirectToRoute('app_message_index');
        }

        return $this->render('message/edit.html.twig', [
            'messageForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', methods: ['POST'], name: 'app_message_delete')]
    public function delete(
        Request $request, 
        #[MapEntity(id: 'id')] Message $message
    ): Response {
        $this->entityManager->remove($message);
        $this->entityManager->flush();

        $this->addFlash('success', 'Message was deleted successfully.');

        return $this->redirectToRoute('app_message_index');
    }

    #[Route('/fetch', name: 'api_message_fetch')]
    public function fetch(): JsonResponse
    {
        try {
            $this->messageFetcher->fetch();
        } catch (\Exception $e) {
            return $this->json(['success' => false]);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/api', name: 'api_message_index')]
    public function getAll(Request $request): JsonResponse
    {
        $queryData = $request->query->all();

        $acceptedColumns = ['id', 'emailId', 'sender', 'receiver', 'receivedAt', 'content'];

        $qb = $this->messageRepository->createQueryBuilder('m');
        $orderColumn = null;
        $orderDir = null;

        if ($queryData['order'][0] ?? null) {
            $orderColumn = $queryData['order'][0]['column'];
            $orderDir = $queryData['order'][0]['dir'] === 'asc';
        }
        
        if ($searched = $queryData['search']['value']) {
            foreach ($queryData['columns'] as $index => $column) {
                if (!in_array($column['data'], $acceptedColumns)) {
                    continue;
                }
                if (!$column['searchable'] === 'true') {
                    continue;
                }

                if (''.$index == $orderColumn && $column['orderable'] === 'true') {
                    $qb->addOrderBy('m.' . $column['data'], $orderDir ? 'asc' : 'desc');
                }

                $qb->orWhere('m.' . $column['data'] . ' LIKE :' . $column['data'])
                    ->setParameter($column['data'], '%'.$searched.'%');
            }
        }

        $records = $qb->setFirstResult($queryData['start'])
            ->setMaxResults($queryData['length'])
            ->getQuery()
            ->getResult()
        ;

        return $this->json([
            'recordsTotal' => $this->messageRepository->count(),
            'recordsFiltered' => count($records),
            'data' => $records,
        ]);
    }
}
