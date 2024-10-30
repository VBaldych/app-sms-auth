<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct($registry, User::class);
    }

    public function findOrCreateUserByPhone(string $phoneNumber): User
    {
        try {
            $user = $this->findOneBy(['phone' => $phoneNumber]);
            if (!$user) {
                $user = new User();
                $user->setPhone($phoneNumber);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_CONFLICT, $e->getMessage());
        }

        return $user;
    }
}
