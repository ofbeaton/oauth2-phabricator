<?php

namespace Ofbeaton\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class PhabricatorResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;
   
    /**
     * Raw data from the response
     *
     * @var array
     */
    protected $data;

    /**
     * Domain
     *
     * @var string
     */
    protected $domain;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->data = $this->getValueByKey($response, 'result', array());
    }

    /**
     * Get resource owner id, alias for phid
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getValueByKey($this->data, 'phid');
    }

    /**
     * Get resource owner phabricator id (phid)
     *
     * @return string|null
     */
    public function getPhid()
    {
        return $this->getValueByKey($this->data, 'phid');
    }

    /**
     * Get resource owner username
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->getValueByKey($this->data, 'userName');
    }

    /**
     * Get resource owner real name
     *
     * @return string|null
     */
    public function getRealName()
    {
        return $this->getValueByKey($this->data, 'realName');
    }

    /**
     * Get resource owner image
     *
     * @return string|null
     */
    public function getImage()
    {
        return $this->getValueByKey($this->data, 'image');
    }

    /**
     * Get resource owner url
     *
     * @return string|null
     */
    public function getUri()
    {
        return $this->getValueByKey($this->data, 'uri');
    }

    /**
     * Get resource owner roles
     *
     * @return string|null
     */
    public function getRoles()
    {
        return $this->getValueByKey($this->data, 'roles');
    }

    /**
     * Get resource owner primary email
     *
     * @return string|null
     */
    public function getPrimaryEmail()
    {
        return $this->getValueByKey($this->data, 'primaryEmail');
    }
    
    /**
     * Set resource owner domain
     *
     * @param  string $domain
     *
     * @return PhabricatorResourceOwner
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }
    
    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}
