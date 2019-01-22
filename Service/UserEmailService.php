<?php

namespace FAC\UserBundle\Service;

use FAC\UserBundle\Entity\User;
use FAC\UserBundle\Entity\UserEmail;
use FAC\UserBundle\Repository\UserEmailRepository;
use DateTime;

class UserEmailService {

    private $repository;

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param UserEmailRepository $repository
     */
    public function __construct(UserEmailRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * Returns the entity given a unique attribute/s.
     * NULL will be returned if the entity does not exist.
     * @param array $attributes
     * @return UserEmail|null
     */
    public function getOneByAttributes(array $attributes) {
        return $this->repository->findOne($attributes);
    }

    ///////////////////////////////////////////
    /// OBJECT FUNCTIONS

    /**
     * Finalize and save the creation of the entity.
     * Returns NULL if some error occurs otherwise it returns the persisted object.
     * @param UserEmail $entity
     * @param User|null $user
     * @param bool $update
     * @return object|bool
     * @throws \Exception
     */
    public function save(UserEmail $entity, User $user = null, $update = false) {
        if(!is_null($user)) {
            $current_time = new \DateTime();
            $current_time->setTimestamp(time());
            if(!$update) {
                $entity->setCreatedOn($current_time);
            }
        }

        $writing = $this->repository->write($entity, $update);
        if(is_array($writing)) {
            return false;
        }

        return $entity;
    }

    /**
     * @param null $user
     * @param null $email
     * @param bool $active
     * @return null|object
     * @throws \Exception
     */
    public function create($user, $email, $active = false) {
        $creation = new DateTime();
        $creation->setTimestamp(time());

        $userEmail = new UserEmail();
        $userEmail->setUser($user);
        $userEmail->setEmail($email);
        $userEmail->setIsActive($active);
        $userEmail->setCreatedOn($creation);

        return $this->save($userEmail);
    }

    /**
     * @param UserEmail $userEmail
     * @param bool $active
     * @return null|object
     * @throws \Exception
     */
    public function updateStatus(UserEmail $userEmail, $active = false) {
        $userEmail->setIsActive($active);
        return $this->save($userEmail);
    }
}