<?php

namespace Ofbeaton\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class PhabricatorResourceOwner implements ResourceOwnerInterface
{
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
        $this->data = $response['result'];
    }

    /**
     * Get resource owner id, alias for phid
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->data['phid'] ?: null;
    }

    /**
     * Get resource owner phabricator id (phid)
     *
     * @return string|null
     */
    public function getPhid()
    {
        return $this->data['phid'] ?: null;
    }

    /**
     * Get resource owner username
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->data['userName'] ?: null;
    }

    /**
     * Get resource owner real name
     *
     * @return string|null
     */
    public function getRealName()
    {
        return $this->data['realName'] ?: null;
    }

    /**
     * Get resource owner image
     *
     * @return string|null
     */
    public function getImage()
    {
        return $this->data['image'] ?: null;
    }

    /**
     * Get resource owner url
     *
     * @return string|null
     */
    public function getUri()
    {
        return $this->data['uri'] ?: null;
    }

    /**
     * Get resource owner roles
     *
     * @return string|null
     */
    public function getRoles()
    {
        return $this->data['roles'] ?: null;
    }

    /**
     * Get resource owner primary email
     *
     * @return string|null
     */
    public function getPrimaryEmail()
    {
        return $this->data['primaryEmail'] ?: null;
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
