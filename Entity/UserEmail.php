<?php

namespace FAC\UserBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use FAC\UserBundle\Entity\User;

/**
 * @ORM\Table(name="`user_emails`")
 * @ORM\Entity(repositoryClass="FAC\UserBundle\Repository\UserEmailRepository")
 * @UniqueEntity(
 *     fields="email",
 *     message="exist.email",
 *     groups={"registration"}
 * )
 */
class UserEmail {

    /**
     * @ORM\Column(name="`id`", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id
     */
    private $id;

    /**
     * @ORM\Column(name="`email`", type="string", length=180, nullable=false)
     * @Assert\NotBlank(message = "require.email")
     * @Assert\Email(
     *     message = "invalid.email",
     *     checkMX = true,
     *     checkHost = true
     * )
     */
    private $email = null;

    /**
     * @ORM\ManyToOne(targetEntity="FAC\UserBundle\Entity\User", inversedBy="userEmails")
     * @ORM\JoinColumn(name="id_users", referencedColumnName="id", nullable=false)
     * @var User $user
     */
    private $user;

    /**
     * @ORM\Column(name="`disabled_on`", type="datetime", nullable=true)
     * @var DateTime $disabledOn
     */
    private $disabledOn;

    /**
     * @ORM\Column(name="`is_disable`", type="boolean", nullable=false, options={"default":0})
     */
    private $isDisable = false;

    /**
     * @ORM\Column(name="`is_active`", type="boolean", nullable=false, options={"default":0})
     */
    private $isActive = false;

    /**
     * @ORM\Column(name="`created_on`", type="datetime", nullable=false)
     * @var DateTime $createdOn
     */
    private $createdOn;

    ################################################# SERIALIZER FUNCTIONS

    /**
     * Returns the array of fields to serialize in entity administration view.
     * @return array
     */
    public function adminSerializer()
    {
        $view_vars = $this->viewSerializer();

        $admin_vars = array();

        return array_merge($view_vars, $admin_vars);
    }

    /**
     * Returns the array of fields to serialize in entity view.
     * @return array
     */
    public function viewSerializer()
    {
        $list_vars = $this->listSerializer();

        $view_vars = array(
        );

        return array_merge($list_vars, $view_vars);
    }

    /**
     * Returns the array of fields to serialize in a list of this entity.
     * @return array
     */
    public function listSerializer()
    {
        $list_vars = array(
        );
        return $list_vars;
    }

    /**
     * Returns the hash code unique identifier of the entity.
     * @return string
     */
    public function hashCode()
    {
        // TODO: Implement hashCode() method.
    }

    ################################################# SERIALIZED FUNCTIONS



    ################################################# GETTERS AND SETTERS FUNCTIONS


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return UserEmail
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set disabledOn.
     *
     * @param \DateTime|null $disabledOn
     *
     * @return UserEmail
     */
    public function setDisabledOn($disabledOn = null)
    {
        $this->disabledOn = $disabledOn;

        return $this;
    }

    /**
     * Get disabledOn.
     *
     * @return \DateTime|null
     */
    public function getDisabledOn()
    {
        return $this->disabledOn;
    }

    /**
     * Set createdOn.
     *
     * @param \DateTime $createdOn
     *
     * @return UserEmail
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * Get createdOn.
     *
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * Set isDisable.
     *
     * @param bool $isDisable
     *
     * @return UserEmail
     */
    public function setIsDisable($isDisable)
    {
        $this->isDisable = $isDisable;

        return $this;
    }

    /**
     * Get isDisable.
     *
     * @return bool
     */
    public function getIsDisable()
    {
        return $this->isDisable;
    }

    /**
     * Set isActive.
     *
     * @param bool $isActive
     *
     * @return UserEmail
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set user.
     *
     * @param \FAC\UserBundle\Entity\User $user
     *
     * @return UserEmail
     */
    public function setUser(\FAC\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \FAC\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
