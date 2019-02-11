<?php

namespace FAC\UserBundle\Controller;


use FOS\OAuthServerBundle\Controller\TokenController as BaseController;
use OAuth2\OAuth2;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use FAC\UserBundle\Service\ClientService;

class TokenController extends BaseController {

    /**
     * @var ClientService $clientService
     */
    private $clientService;

    /**
     * TokenController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container ) {
        $server = $container->get("fos_oauth_server.server.public");

        parent::__construct($server);
        $this->clientService = $container->get("FAC\UserBundle\Service\ClientService");
    }

    public function tokenAction(Request $request) {

        $result = parent::tokenAction($request);

        return $result;
    }
}