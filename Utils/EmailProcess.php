<?php

namespace FAC\UserBundle\Utils;

use FAC\UserBundle\Entity\User;
use Swift_Mailer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;

class EmailProcess implements EmailProcessInterface
{

    private $container;
    private $swiftMailer;
    private $templating;

    public function __construct(ContainerInterface $container, Swift_Mailer $swiftMailer, Twig_Environment $templating)
    {
        $this->container = $container;
        $this->swiftMailer = $swiftMailer;
        $this->templating = $templating;
    }

    /**
     * @param null $recipient
     * @param $subject
     * @param $body
     * @param User|null $user
     * @param null $when
     * @param null $type
     * @param null $sendOn
     * @return bool
     */
    public function emailProcess($recipient, $subject, $body, User $user = null, $when = null, $sendOn = null) {

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($recipient)
            ->setTo($user->getEmail())
            ->setContentType('text/html')
            ->setBody($body)
        ;

        if(!Utils::checkEmailString($user->getEmail())) {
            return false;
        }
        else {
            if (!$this->swiftMailer->send($message)) {
                return false;
            }
        }

        return true;
    }
}
