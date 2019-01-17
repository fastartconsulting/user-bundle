<?php

namespace UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use EmailBundle\Entity\Email;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use DateTime;

/**
 * @ORM\Table(name="`users`")
 * @ORM\Entity
 * @UniqueEntity(
 *     fields="email",
 *     message="exist.email",
 *     groups={"registration"}
 * )
 */
class User extends BaseUser {

    /**
     * @ORM\Column(name="`id`", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id
     */
    protected $id;

    /**
     * @Assert\NotBlank(message = "require.email", groups={"registration"})
     * @Assert\Email(
     *     message = "invalid.email",
     *     checkMX = true,
     *     checkHost = true,
     *     groups={"registration"}
     * )
     */
    protected $email;

    /**
     * @Assert\NotBlank(message = "require.password", groups={"registration"})
     * @Assert\Length(
     *      min = 8,
     *      max = 20,
     *      minMessage = "min.length.password",
     *      maxMessage = "max.length.password",
     *      groups={"registration"}
     * )
     * @Assert\Regex(
     *     pattern="/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d!#$%&?]*$/",
     *     message="special.chars.password",
     *     groups={"registration"}
     * )
     */
    protected $plainPassword;

    /**
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    private $facebookId;

    private $facebookAccessToken;

    /**
     * @ORM\Column(name="googleplus_id", type="string", length=255, nullable=true)
     */
    private $googleplusId;

    private $googleplusAccessToken;

    /**
     * @ORM\OneToMany(targetEntity="EmailBundle\Entity\Email", mappedBy="user")
     */
    private $emails;

    /**
     * @ORM\OneToMany(targetEntity="UserBundle\Entity\UserEmail", mappedBy="user")
     */
    private $userEmails;

    /**
     * @var boolean
     */
    private $locked;

    /**
     * @var boolean
     */
    private $expired;

    /**
     * @var \DateTime
     */
    private $expiresAt;

    /**
     * @var boolean
     */
    private $credentialsExpired;

    /**
     * @var \DateTime
     */
    private $credentialsExpireAt;

    /**
     * @ORM\Column(name="`created_on`", type="datetime", nullable=true)
     * @var DateTime $createdOn
     */
    private $createdOn;

    /**
     * @ORM\Column(name="`edited_by`", type="integer", nullable=true)
     */
    private $editedBy;

    /**
     * @ORM\Column(name="`edited_on`", type="datetime", nullable=true)
     * @var DateTime $editedOn
     */
    private $editedOn;

    /**
     * @ORM\Column(name="`disabled_by`", type="integer", nullable=true)
     */
    private $disabledBy;

    /**
     * @ORM\Column(name="`disabled_on`", type="datetime", nullable=true)
     * @var DateTime $disabledOn
     */
    private $disabledOn;

    ################################################# SERIALIZER FUNCTIONS

    public function jsonSerialize()
    {
        return $this->listSerializer();
    }

    public function adminSerializer()
    {
        $view_vars = $this->viewSerializer();

        $admin_vars = array(
            'locked'                => $this->serializedLocked(),
        );

        return array_merge($view_vars, $admin_vars);
    }

    public function viewSerializer()
    {
        $list_vars = $this->listSerializer();

        $view_vars = array(
            'credential_expiration' => $this->serializedCredentialExpiration(),
            'confirm_token'    => $this->getConfirmationToken() //FOR TESTS
        );

        return array_merge($list_vars, $view_vars);
    }

    public function listSerializer()
    {
        $list_vars = array(
            'id'                    => $this->serializedId(),
            'email'                 => $this->serializedEmail(),
            'admin'                 => $this->serializedAdmin(),
            'credentialsExpired'   => $this->isCredentialsExpired(),
            'enabled'               => $this->serializedEnabled(),
        );
        return $list_vars;
    }

    ################################################# SERIALIZED FUNCTIONS

    /**
     * if user is locked or not
     * @JMS\VirtualProperty
     * @JMS\SerializedName("locked")
     * @JMS\Type("boolean")
     * @JMS\Groups({"admin"})
     * @JMS\Since("1.0.x")
     */
    public function serializedLocked()
    {
        return $this->isLocked();
    }

    /**
     * user credential expiration
     * @JMS\VirtualProperty
     * @JMS\SerializedName("credential_expiration")
     * @JMS\Type("string")
     * @JMS\Groups({"admin","view"})
     * @JMS\Since("1.0.x")
     */
    public function serializedCredentialExpiration()
    {
        return (is_null($this->credentialsExpireAt)?null:strftime('%Y-%m-%d %H:%M',$this->credentialsExpireAt->getTimestamp()));
    }

    /**
     * user id
     * @JMS\VirtualProperty
     * @JMS\SerializedName("id")
     * @JMS\Type("integer")
     * @JMS\Groups({"admin","view","list"})
     * @JMS\Since("1.0.x")
     */
    public function serializedId()
    {
        return (is_null($this->id)?null:$this->id);
    }

    /**
     * user email
     * @JMS\VirtualProperty
     * @JMS\SerializedName("email")
     * @JMS\Type("string")
     * @JMS\Groups({"admin","view","list"})
     * @JMS\Since("1.0.x")
     */
    public function serializedEmail()
    {
        return (is_null($this->email)?null:$this->email);
    }

    /**
     * if user is an admin
     * @JMS\VirtualProperty
     * @JMS\SerializedName("admin")
     * @JMS\Type("boolean")
     * @JMS\Groups({"admin","view","list"})
     * @JMS\Since("1.0.x")
     */
    public function serializedAdmin()
    {
        return ($this->hasRole("ROLE_ADMIN")?true:false);
    }

    /**
     * if user credentials are expired or not
     * @JMS\VirtualProperty
     * @JMS\SerializedName("credential_expired")
     * @JMS\Type("boolean")
     * @JMS\Groups({"admin","view","list"})
     * @JMS\Since("1.0.x")
     */
    public function serializedCredentialExpired()
    {
        return $this->isCredentialsExpired();
    }

    /**
     * if user is enabled or not
     * @JMS\VirtualProperty
     * @JMS\SerializedName("credential_expired")
     * @JMS\Type("boolean")
     * @JMS\Groups({"admin","view","list"})
     * @JMS\Since("1.0.x")
     */
    public function serializedEnabled()
    {
        return $this->isEnabled();
    }

    ################################################# UTILS FUNCTIONS

    /**
     * @param \DateInterval $interval
     *
     * @return int
     */
    public static function getSeconds(\DateInterval $interval)
    {
        $datetime = new \DateTime('@0');
        return $datetime->add($interval)->getTimestamp();
    }

    public function isAccountNonExpired()
    {
        if (true === $this->expired) {
            return false;
        }
        if (null !== $this->expiresAt && $this->getSeconds($this->expiresAt->diff(new \DateTime())) <= 0) {
            return false;
        }
        return true;
    }

    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    public function isCredentialsNonExpired()
    {
        if (true === $this->credentialsExpired) {
            return false;
        }
        if (null !== $this->credentialsExpireAt && $this->credentialsExpireAt->getTimestamp() < time()) {
            return false;
        }
        return true;
    }

    public function isCredentialsExpired()
    {
        return !$this->isCredentialsNonExpired();
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function isExpired()
    {
        return !$this->isAccountNonExpired();
    }

    public function isLocked()
    {
        return !$this->isAccountNonLocked();
    }


    ################################################# GETTERS AND SETTERS FUNCTIONS

    public function __construct() {
        parent::__construct();
        $this->enabled = false;
        $this->locked = false;
        $this->expired = false;
        $this->credentialsExpired = false;
        $this->emails = new ArrayCollection();
    }

    /**
     * @param string $facebookId
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @param string $facebookAccessToken
     * @return User
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;

        return $this;
    }

    /**
     * @param string $googleplusId
     * @return User
     */
    public function setGoogleplusId($googleplusId)
    {
        $this->googleplusId = $googleplusId;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleplusId()
    {
        return $this->googleplusId;
    }

    /**
     * @param string $googleplusAccessToken
     * @return User
     */
    public function setGoogleplusAccessToken($googleplusAccessToken)
    {
        $this->googleplusAccessToken = $googleplusAccessToken;

        return $this;
    }

    /**
     * Add email.
     *
     * @param Email $email
     *
     * @return User
     */
    public function addEmail(Email $email)
    {
        $this->emails[] = $email;

        return $this;
    }

    /**
     * Remove email.
     *
     * @param Email $email
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeEmail(Email $email)
    {
        return $this->emails->removeElement($email);
    }

    /**
     * Get emails.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param $boolean
     * @return $this
     */
    public function setLocked($boolean)
    {
        $this->locked = $boolean;
        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return User
     */
    public function setCredentialsExpireAt(\DateTime $date)
    {
        $this->credentialsExpireAt = $date;
        return $this;
    }
    /**
     * @param boolean $boolean
     *
     * @return User
     */
    public function setCredentialsExpired($boolean)
    {
        $this->credentialsExpired = $boolean;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param DateTime $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @return mixed
     */
    public function getEditedBy()
    {
        return $this->editedBy;
    }

    /**
     * @param mixed $editedBy
     */
    public function setEditedBy($editedBy)
    {
        $this->editedBy = $editedBy;
    }

    /**
     * @return DateTime
     */
    public function getEditedOn()
    {
        return $this->editedOn;
    }

    /**
     * @param DateTime $editedOn
     */
    public function setEditedOn($editedOn)
    {
        $this->editedOn = $editedOn;
    }

    /**
     * @return mixed
     */
    public function getDisabledBy()
    {
        return $this->disabledBy;
    }

    /**
     * @param mixed $disabledBy
     */
    public function setDisabledBy($disabledBy)
    {
        $this->disabledBy = $disabledBy;
    }

    /**
     * @return DateTime
     */
    public function getDisabledOn()
    {
        return $this->disabledOn;
    }

    /**
     * @param DateTime $disabledOn
     */
    public function setDisabledOn($disabledOn)
    {
        $this->disabledOn = $disabledOn;
    }





    /**
     * Add userEmail.
     *
     * @param \UserBundle\Entity\UserEmail $userEmail
     *
     * @return User
     */
    public function addUserEmail(\UserBundle\Entity\UserEmail $userEmail)
    {
        $this->userEmails[] = $userEmail;

        return $this;
    }

    /**
     * Remove userEmail.
     *
     * @param \UserBundle\Entity\UserEmail $userEmail
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeUserEmail(\UserBundle\Entity\UserEmail $userEmail)
    {
        return $this->userEmails->removeElement($userEmail);
    }

    /**
     * Get userEmails.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserEmails()
    {
        return $this->userEmails;
    }
}
