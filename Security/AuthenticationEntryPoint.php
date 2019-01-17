<?php

namespace FAC\UserBundle\Security;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface {

    private $translator;

    public function __construct(TranslatorInterface $translator){
        $this->translator = $translator;
    }

    /**
     * Starts the authentication scheme.
     *
     * @param Request $request The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     *
     * @return JsonResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $response = new JsonResponse();
        $response->setStatusCode(401);
        $response->setData(array(
            "status"  => 401,
            "message" => $this->translator->trans('user.unauthorized'))
        );
        return $response;
    }
}