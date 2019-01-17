<?php

namespace FAC\UserBundle\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private $translator;

    public function __construct(TranslatorInterface $translator){
        $this->translator = $translator;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $response = new JsonResponse();
        $response->setStatusCode(403);
        $response->setData(array(
            "status"  => 403,
            "message" => $this->translator->trans('user.not.rights'))
        );
        return $response;
    }
}