<?php

namespace Ofbeaton\OAuth2\Client\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class PhabricatorIdentityProviderException extends IdentityProviderException
{
   /**
     * Creates client exception from response.
     *
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     *
     * @return PhabricatorIdentityProviderException
     */
    public static function clientException(ResponseInterface $response, $data)
    {
        return static::fromResponse(
            $response,
            isset($data['error']) ? ($data['error'].': '.$data['error_description']) : $response->getReasonPhrase()
        );
    }

     /**
     * Creates oauth exception from response.
     *
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     *
     * @return PhabricatorIdentityProviderException
     */
    public static function oauthException(ResponseInterface $response, $data)
    {
        return static::fromResponse(
            $response,
            isset($data['error']) ? ($data['error'].': '.$data['error_description']) : $response->getReasonPhrase()
        );
    }

    /**
     * Creates identity exception from response.
     *
     * @param  ResponseInterface $response
     * @param  string $message
     *
     * @return PhabricatorIdentityProviderException
     */
    protected static function fromResponse(ResponseInterface $response, $message = null)
    {
        return new static($message, $response->getStatusCode(), (string)$response->getBody());
    }
}
