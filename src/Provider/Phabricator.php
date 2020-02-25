<?php

namespace Ofbeaton\OAuth2\Client\Provider;

use Ofbeaton\OAuth2\Client\Provider\Exception\PhabricatorIdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Phabricator extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Phabricator API endpoint to retrieve logged in user information.
     *
     * @var string
     */
    const PATH_API_USER = '/api/user.whoami';
    
    /**
     * Phabricator OAuth server authorization endpoint.
     *
     * @var string
     */
    const PATH_AUTHORIZE = '/oauthserver/auth/';
    
    /**
     * Phabricator OAuth server token request endpoint.
     *
     * @var string
     */
    const PATH_TOKEN = '/oauthserver/token/';

    /**
     * Domain
     *
     * @var string
     */
    protected $domain;

    /**
     * @param array $options
     * @param array $collaborators
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
        if (empty($options['domain'])) {
            $message = 'The "domain" option not set. Please set a domain.';
            throw new \InvalidArgumentException($message);
        }
    }

    /**
     * Get domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        $url = $this->domain.self::PATH_AUTHORIZE;
        return $url;
    }

    /**
     * Get access token url to retrieve token
     *
     * @param  array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        $url = $this->domain.self::PATH_TOKEN;
        return $url;
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $url = $this->domain.self::PATH_API_USER.'?'.$this->buildQueryString(array(
            'access_token' => $token->getToken(),
        ));
        return $url;
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw PhabricatorIdentityProviderException::clientException($response, $data);
        } elseif (isset($data['error']) === true) {
            throw PhabricatorIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return Ofbeaton\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new PhabricatorResourceOwner($response);
        $user->setDomain($this->domain);
        return $user;
    }
}
