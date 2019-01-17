<?php

namespace UserBundle\Entity;

use DateTime;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table("clients")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\ClientRepository")
 * @ORM\Entity
 */
class Client extends BaseClient {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="`keyword`", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message = "require.keyword")
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9\-]*$/",
     *     message="invalid.keyword"
     * )
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "min.length.keyword",
     *      maxMessage = "max.length.keyword"
     * )
     */
    private $keyword;

    /**
     * @ORM\Column(name="`disabled_by`", type="integer", nullable=true)
     */
    private $disabledBy;

    /**
     * @ORM\Column(name="`disabled_on`", type="datetime", nullable=true)
     * @var DateTime $disabledOn
     */
    private $disabledOn;

    /**
     * @ORM\Column(name="`is_disable`", type="boolean", nullable=false, options={"default":0})
     */
    private $isDisable = false;



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
            'id'                => $this->serializedId(),
            'keyword'           => $this->serializedKeyword(),
            'randomId'          => $this->serializedRandomId(),
            'secret'            => $this->serializedSecret(),
            'redirectUris'      => $this->serializedRedirectUris(),
            'allowedGrantTypes' => $this->serializedAllowedGrantTypes()
        );

        return array_merge($list_vars, $view_vars);
    }

    /**
     * Returns the array of fields to serialize in a list of this entity.
     * @return array
     */
    public function listSerializer() {
        $export_vars = array();
        return $export_vars;
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

    /**
     * log id
     * @JMS\VirtualProperty
     * @JMS\SerializedName("id")
     * @JMS\Type("integer")
     * @JMS\Since("1.0.x")
     */
    public function serializedId() {
        return (is_null($this->id)?null:$this->id);
    }

    /**
     * log keyword
     * @JMS\VirtualProperty
     * @JMS\SerializedName("keyword")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedKeyword()
    {
        return (is_null($this->keyword)?null:$this->keyword);
    }

    /**
     * log randomId
     * @JMS\VirtualProperty
     * @JMS\SerializedName("randomId")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedRandomId() {
        return (is_null($this->randomId)?null:$this->randomId);
    }

    /**
     * log secret
     * @JMS\VirtualProperty
     * @JMS\SerializedName("secret")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedSecret() {
        return (is_null($this->secret)?null:$this->secret);
    }

    /**
     * log redirectUris
     * @JMS\VirtualProperty
     * @JMS\SerializedName("redirectUris")
     * @JMS\Type("array")
     * @JMS\Since("1.0.x")
     */
    public function serializedRedirectUris() {
        return (is_null($this->redirectUris)?null:$this->redirectUris);
    }

    /**
     * log allowedGrantTypes
     * @JMS\VirtualProperty
     * @JMS\SerializedName("allowedGrantTypes")
     * @JMS\Type("array")
     * @JMS\Since("1.0.x")
     */
    public function serializedAllowedGrantTypes() {
        return (is_null($this->allowedGrantTypes)?null:$this->allowedGrantTypes);
    }


    ################################################# GETTERS AND SETTERS FUNCTIONS


    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Set keyword
     *
     * @param string $keyword
     *
     * @return Client
     */
    public function setKeyword($keyword) {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword
     *
     * @return string
     */
    public function getKeyword() {
        return $this->keyword;
    }

    /**
     * @return mixed
     */
    public function getDisabledBy() {
        return $this->disabledBy;
    }

    /**
     * @param mixed $disabledBy
     */
    public function setDisabledBy($disabledBy) {
        $this->disabledBy = $disabledBy;
    }

    /**
     * @return DateTime
     */
    public function getDisabledOn() {
        return $this->disabledOn;
    }

    /**
     * @param DateTime $disabledOn
     */
    public function setDisabledOn($disabledOn) {
        $this->disabledOn = $disabledOn;
    }

    /**
     * @return mixed
     */
    public function getIsDisable() {
        return $this->isDisable;
    }

    /**
     * @param mixed $isDisable
     */
    public function setIsDisable($isDisable) {
        $this->isDisable = $isDisable;
    }


}