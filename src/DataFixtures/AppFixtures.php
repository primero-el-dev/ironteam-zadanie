<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setEmail($_ENV['APP_USER_EMAIL'])
            // I know I could user UserPasswordHasherInterface, but this version is simpler, suits our need
            // and doesn't depend on non-necessary services
            ->setPassword(password_hash($_ENV['APP_USER_PASSWORD'], PASSWORD_DEFAULT))
            ->addRole('ROLE_ADMIN')
        ;

        $manager->persist($user);
        $manager->flush();
    }
}
