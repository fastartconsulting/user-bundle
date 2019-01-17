<?php

namespace FAC\UserBundle\Entity;

use DateTime;
use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("access_tokens")
 * @ORM\Entity
 */
class AccessToken extends BaseAccessToken {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(name="`timestamp`", type="datetime", nullable=false)
     * @var DateTime $timestamp
     */
    private $timestamp;

    /**
     * Set timestamp
     *
     * @param \DateTime $timestamp
     *
     * @return AccessToken
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $creation = new DateTime();
        $creation->setTimestamp(time());
        $this->setTimestamp($creation);
    }
}