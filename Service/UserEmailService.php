<?php

namespace UserBundle\Service;

use UserBundle\Entity\UserEmail;
use UserBundle\Repository\UserEmailRepository;
use LogBundle\Service\LogMonitorService;
use Schema\Entity;
use Schema\EntityService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use DateTime;

class UserEmailService extends EntityService {


    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param UserEmailRepository $repository
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param LogMonitorService $logMonitorService
     */
    public function __construct(UserEmailRepository $repository, AuthorizationCheckerInterface $authorizationChecker, LogMonitorService $logMonitorService) {
        $this->repository = $repository;
        parent::__construct($repository, $authorizationChecker, $logMonitorService);
    }

    /**
     * Returns true if the logged user is the creator of this entity.
     * @param Entity $entity
     * @return bool
     */
    public function isOwner(Entity $entity)
    {
        // TODO: Implement isOwner() method.
    }

    /**
     * Returns true if the logged user can administrate the entity
     * @param Entity $entity
     * @return bool
     */
    public function canAdmin(Entity $entity)
    {
        // TODO: Implement canAdmin() method.
    }

    /**
     * Returns true if the logged user can POST the entity
     * @return bool
     */
    public function canPost()
    {
        // TODO: Implement canPost() method.
    }

    /**
     * Returns true if the logged user can PUT the entity
     * @param Entity $entity
     * @return bool
     */
    public function canPut(Entity $entity)
    {
        // TODO: Implement canPut() method.
    }

    /**
     * Returns true if the logged user can PATCH the entity
     * @param Entity $entity
     * @return bool
     */
    public function canPatch(Entity $entity)
    {
        // TODO: Implement canPatch() method.
    }

    /**
     * Returns true if the logged user can DELETE the entity
     * @param Entity $entity
     * @return bool
     */
    public function canDelete(Entity $entity)
    {
        // TODO: Implement canDelete() method.
    }

    /**
     * Returns true if the logged user can GET the entity
     * @param Entity $entity
     * @return bool
     */
    public function canGet(Entity $entity)
    {
        // TODO: Implement canGet() method.
    }

    /**
     * Returns true if the logged user can GET a list of this entity
     * @return bool
     */
    public function canGetList()
    {
        // TODO: Implement canGetList() method.
    }

    /**
     * @param null $user
     * @param null $email
     * @param bool $active
     * @return null|object
     * @throws \Doctrine\DBAL\ConnectionException
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
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function updateStatus(UserEmail $userEmail, $active = false) {
        $userEmail->setIsActive($active);
        return $this->save($userEmail);
    }
}