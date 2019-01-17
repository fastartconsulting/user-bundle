<?php

namespace FAC\UserBundle\Service;


use LogBundle\Service\LogMonitorService;
use Schema\Entity;
use Schema\EntityService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use FAC\UserBundle\Entity\Client;
use FAC\UserBundle\Repository\ClientRepository;
use Utils\LogUtils;

class ClientService extends EntityService {

    /** @var LogMonitorService $log_monitor */
    protected $log_monitor;

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * ClientService constructor.
     * @param ClientRepository $repository
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param LogMonitorService $logMonitorService
     */
    public function __construct(ClientRepository $repository,
                                AuthorizationCheckerInterface $authorizationChecker,
                                LogMonitorService $logMonitorService
    ) {
        $this->repository  = $repository;
        parent::__construct($repository, $authorizationChecker, $logMonitorService);

        $this->log_monitor = $logMonitorService;
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
     * @param $clientManager
     * @param Client $client
     * @return Client|array
     */
    public function create($clientManager, Client $client){
        $newClient = $clientManager->createClient();
        $newClient->setKeyword($client->getKeyword());
        $newClient->setRedirectUris($client->getRedirectUris());
        $newClient->setAllowedGrantTypes($client->getAllowedGrantTypes());

        try{
            $clientManager->updateClient($newClient);
        }catch (\Exception $e){
            $exception = LogUtils::getFormattedExceptions($e);
            return $exception;
        }

        return $client;
    }

    /**
     * @param null $clientId
     * @return mixed|null
     */
    public function getByClientId($clientId=null) {

        $client = null;

        if(!is_null($clientId)) {

            $clientArray = explode("_",$clientId);
            $id          = $clientArray[0];
            $randomId    = $clientArray[1];

            $client = $this->repository->findByClientId($id, $randomId);
        }

        return $client;
    }

    /**
     * @param $client
     * @return bool
     */
    public function isExternalClient (&$client) {

        if(!is_null($client)) {

            /**
             * Get Keyword of client and check if it is for Cardio
             */
            $keyword = $client->getKeyword();

            if(stripos($keyword,"cardio") !== false) {
                return true;
            }
        }

        return false;
    }

}