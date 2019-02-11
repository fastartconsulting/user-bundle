<?php


namespace FAC\UserBundle\Utils;

use FAC\UserBundle\Entity\User;
use Swift_Mailer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;

interface EmailProcessInterface
{

    /**
     * EmailProcessInterface constructor.
     * @param ContainerInterface $container
     * @param Swift_Mailer $swiftMailer
     * @param Twig_Environment $templating
     */
    public function __construct(ContainerInterface $container, Swift_Mailer $swiftMailer, Twig_Environment $templating);

    /**
     * @param $recipient
     * @param $subject
     * @param $body
     * @param User|null $user
     * @param null $when
     * @param null $sendOn
     * @return bool
     */
    public function emailProcess($recipient, $subject, $body, User $user = null, $when = null, $sendOn = null);
}
