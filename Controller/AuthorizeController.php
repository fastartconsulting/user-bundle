<?php

namespace FAC\UserBundle\Controller;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use FOS\OAuthServerBundle\Form\Handler\AuthorizeFormHandler;
use FOS\OAuthServerBundle\Model\ClientInterface;
use OAuth2\OAuth2ServerException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FAC\UserBundle\Utils\ResponseUtils;


class AuthorizeController extends FOSRestController {
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * AuthorizeController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container ) {
        $this->container = $container;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @Rest\Get("/private/oauth/v2/auth")
     * @Rest\Post("/private/oauth/v2/auth")
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     * @throws \OAuth2\OAuth2RedirectException
     */
    public function authorizeAction(Request $request)
    {
        $user = $this->getTokenStorage()->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        if (true === $this->container->get('session')->get('_fos_oauth_server.ensure_logout')) {
            $this->container->get('session')->invalidate(600);
            $this->container->get('session')->set('_fos_oauth_server.ensure_logout', true);
        }

        $form = $this->container->get('fos_oauth_server.authorize.form');
        $formHandler = $this->container->get('fos_oauth_server.authorize.form.handler');

        $event = $this->container->get('event_dispatcher')->dispatch(
            OAuthEvent::PRE_AUTHORIZATION_PROCESS,
            new OAuthEvent($user, $this->getClient())
        );

        if ($event->isAuthorizedClient()) {
            $scope = $request->get('scope', null);

            return $this->container
                ->get('fos_oauth_server.server')
                ->finishClientAuthorization(true, $user, $request, $scope);
        }

        if (true === $formHandler->process()) {
            return $this->processSuccess($user, $formHandler, $request);
        }

        $response = new ResponseUtils($this->get("translator"));

        $data = $form->getData();
        $dataArray = $this->getDataArray($data);

        return $response->getResponse($dataArray);

    }

    /**
     * @param $data
     * @return mixed
     */
    private function getDataArray(&$data){
        $formatted["accepted"] = isset($data->accepted)?$data->accepted:null;
        $formatted["client_id"] = isset($data->client_id)?$data->client_id:null;
        $formatted["response_type"] = isset($data->response_type)?$data->response_type:null;
        $formatted["redirect_uri"] = isset($data->redirect_uri)?$data->redirect_uri:null;
        $formatted["state"] = isset($data->state)?$data->state:null;
        $formatted["scope"] = isset($data->scope)?$data->scope:null;
        return $formatted;
    }

    /**
     * @param UserInterface        $user
     * @param AuthorizeFormHandler $formHandler
     * @param Request              $request
     *
     * @return Response
     */
    protected function processSuccess(UserInterface $user, AuthorizeFormHandler $formHandler, Request $request)
    {
        if (true === $this->container->get('session')->get('_fos_oauth_server.ensure_logout')) {
            $this->getTokenStorage()->setToken(null);
            $this->container->get('session')->invalidate();
        }

        $this->container->get('event_dispatcher')->dispatch(
            OAuthEvent::POST_AUTHORIZATION_PROCESS,
            new OAuthEvent($user, $this->getClient(), $formHandler->isAccepted())
        );

        $formName = $this->container->get('fos_oauth_server.authorize.form')->getName();
        if (!$request->query->all() && $request->request->has($formName)) {
            $request->query->add($request->request->get($formName));
        }

        try {
            return $this->container
                ->get('fos_oauth_server.server')
                ->finishClientAuthorization($formHandler->isAccepted(), $user, $request, $formHandler->getScope());
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }

    /**
     * Generate the redirection url when the authorize is completed.
     *
     * @param UserInterface $user
     *
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('fos_oauth_server_profile_show');
    }

    /**
     * @return ClientInterface.
     */
    protected function getClient()
    {
        if (null === $this->client) {
            $request = $this->getCurrentRequest();

            $client = null;
            if (null !== $request) {
                if (null === $clientId = $request->get('client_id')) {
                    $form = $this->container->get('fos_oauth_server.authorize.form');
                    $formData = $request->get($form->getName(), array());
                    $clientId = isset($formData['client_id']) ? $formData['client_id'] : null;
                }

                $client = $this->container
                    ->get('fos_oauth_server.client_manager')
                    ->findClientByPublicId($clientId);
            }

            if (null === $client) {
                throw new NotFoundHttpException('Client not found.');
            }

            $this->client = $client;
        }

        return $this->client;
    }

    private function getCurrentRequest()
    {
        if ($this->container->has('request_stack')) {
            $request = $this->container->get('request_stack')->getCurrentRequest();
            if (null === $request) {
                throw new \RuntimeException('No current request.');
            }

            return $request;
        } else {
            return $this->container->get('request');
        }
    }

    private function getTokenStorage()
    {
        if ($this->container->has('security.token_storage')) {
            return $this->container->get('security.token_storage');
        }

        return $this->container->get('security.context');
    }
}
