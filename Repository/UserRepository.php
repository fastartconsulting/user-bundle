<?php

namespace UserBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use LogBundle\Document\LogMonitor;
use LogBundle\Service\LogMonitorService;
use ResourceBundle\Entity\CalendarTimezone;
use Schema\SchemaEntityRepository;
use UserBundle\Entity\User;
use UserBundle\Service\UserService;
use Utils\LogUtils;

class UserRepository  {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry) {

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
                /*$profile = $this->profileService->create($user, $calendarTimezone, $keyword);*/
                $user = $userService->sendMailRegistrationConfirm($user, $token);
            } catch (\Exception $e) {
                $exception = LogUtils::getFormattedExceptions($e);
                $this->getEntityManager()->getConnection()->rollBack();
                return $exception;
            }

            $this->getEntityManager()->getConnection()->commit();

        } catch (\Exception $e) {
            $exception = LogUtils::getFormattedExceptions($e);

            /*if(is_null($profile)) {
                $this->profileService->forceDelete($profile);
            }*/
            $this->getEntityManager()->getConnection()->rollBack();
            return $exception;
        }

        return $user;
    }

    /**
     * @param  User $user
     * @param  $profile
     * @return array|null|User
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function enableUser(User $user, $profile) {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            $this->getEntityManager()->persist($user);

            /*try {
                $this->getEntityManager()->flush();
                //$result = $this->profileService->enabling($profile, $user, 'enable');
                $result = null;
                if(!$result || is_null($result)){
                    $exception = array(
                        'backtrace'        => '',
                        'file'             => 'UserRepository',
                        'line'             => '86',
                        'exceptionMessage' => 'Failed save of enabling Profile'
                    );
                    $this->getEntityManager()->getConnection()->rollBack();
                    return $exception;
                }
            } catch (\Exception $e) {
                $exception = LogUtils::getFormattedExceptions($e);

                $this->getEntityManager()->getConnection()->rollBack();
                return $exception;
            }*/

            $this->getEntityManager()->getConnection()->commit();

        } catch (\Exception $e) {
            $exception = LogUtils::getFormattedExceptions($e);

            $this->getEntityManager()->getConnection()->rollBack();
            return $exception;
        }

        return $user;
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
     */
    public function findAllOldPending () {

        $date    = new \DateTime();
        $results = array();

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