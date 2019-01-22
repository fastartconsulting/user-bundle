<?php

namespace FAC\UserBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use FAC\UserBundle\Entity\User;
use FAC\UserBundle\Service\UserService;
use FAC\UserBundle\Utils\Utils;

class UserRepository extends ServiceEntityRepository  {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, User::class);
    }

    ///////////////////////////////////////////
    /// OBJECT FUNCTIONS

    /**
     * Saves a given entity.
     * @param User $entity
     * @param bool $update
     * @return bool|array
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function write(User $entity, $update = false) {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            if(!$update) {
                $this->getEntityManager()->persist($entity);
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->getConnection()->commit();
        } catch (\Exception $e) {
            $exception = Utils::getFormattedExceptions($e);
            $this->getEntityManager()->getConnection()->rollBack();
            return $exception;
        }

        return true;
    }

    /**
     * @param  User $user
     * @param  $token
     * @param  UserService $userService
     * @return array|null|User
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function saveUser(User $user, $token, $userService) {

        $profile = null;
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            $this->getEntityManager()->persist($user);

            try {
                $this->getEntityManager()->flush();

                $user = $userService->sendMailRegistrationConfirm($user, $token);
            } catch (\Exception $e) {
                $exception = Utils::getFormattedExceptions($e);
                $this->getEntityManager()->getConnection()->rollBack();
                return $exception;
            }

            $this->getEntityManager()->getConnection()->commit();

        } catch (\Exception $e) {
            $exception = Utils::getFormattedExceptions($e);

            $this->getEntityManager()->getConnection()->rollBack();
            return $exception;
        }

        return $user;
    }

    /**
     * @param  User $user
     * @return array|null|User
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function enableUser(User $user) {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            $this->getEntityManager()->persist($user);

            $this->getEntityManager()->getConnection()->commit();

        } catch (\Exception $e) {
            $exception = Utils::getFormattedExceptions($e);

            $this->getEntityManager()->getConnection()->rollBack();
            return $exception;
        }

        return $user;
    }

    ///////////////////////////////////////////
    /// FIND FUNCTIONS

    /**
     * Find one object by attributes.
     * @param array $criteria
     * @return User|null
     */
    public function findOne(array $criteria) {
        $object = null;

        if(count($criteria) > 0) {

            /** @var User $object */
            $object = $this->findOneBy(
                $criteria
            );
        }

        return $object;
    }

    /**
     * Find an user given its email.
     * NULL will be returned if the user does not exist or it is locked or its creator is locked.
     * @param string $email
     * @return User|object
     */
    public function findByEmail($email) {
        /** @var User $user */
        $user = $this->findOneBy(array('email'=>$email));
        if(!$user) {
            return null;
        }

        return $user;
    }

    /**
     * @param $username
     * @return null|User
     */
    public function findByUsername($username) {
        /** @var User $user */
        $user = $this->findOneBy(array('username'=>$username));
        if(!$user) {
            return null;
        }

        return $user;
    }

    /**
     * @param User $user
     */
    public function findJustSentConfirmation(User $user) {

    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function findAllOldPending () {

        $date    = new \DateTime();

        $oldDate = $date->modify('-1 year')->format('Y-m-d');

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('u')
            ->from('UserBundle:User', 'u')
            ->where('u.enabled=0 AND u.createdOn <= :oldDate')
            ->setParameter('oldDate', $oldDate)
        ;

        $results = $qb->getQuery()->getResult();

        return $results;
    }


}