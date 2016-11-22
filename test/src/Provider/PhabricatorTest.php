<?php 

namespace Ofbeaton\OAuth2\Client\Test\Provider;

use Mockery as m;

class PhabricatorTest extends \PHPUnit_Framework_TestCase
{
    protected $domain;   
    protected $provider;

    protected function setUp()
    {
        $this->domain = 'https://'.uniqid().'company.com';
        $this->provider = new \Ofbeaton\OAuth2\Client\Provider\Phabricator([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'domain' => $this->domain,
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }
    
    public function testDomain()
    {
        $domain = $this->provider->getDomain();

        $this->assertEquals($this->domain, $domain);
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes()
    {
        $options = ['scope' => [uniqid(),uniqid()]];

        $url = $this->provider->getAuthorizationUrl($options);

        $this->assertContains(urlencode(implode(',', $options['scope'])), $url);
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauthserver/auth/', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauthserver/token/', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"access_token":"mock_access_token", "scope":"", "token_type":"bearer"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testPhabricatorDomainUrls()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn('access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&otherKey={1234}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals($this->domain.'/oauthserver/auth/', $this->provider->getBaseAuthorizationUrl());
        $this->assertEquals($this->domain.'/oauthserver/token/', $this->provider->getBaseAccessTokenUrl([]));
        $this->assertEquals($this->domain.'/api/user.whoami?access_token=mock_access_token', $this->provider->getResourceOwnerDetailsUrl($token));
    }

    public function testUserData()
    {
        $phid = uniqid();
        $userName = uniqid();
        $realName = uniqid();
        $image = uniqid();
        $uri = uniqid();
        $roles = array(
            uniqid(),
            uniqid(),
            uniqid(),
            uniqid(),
        );
        $primaryEmail = uniqid();

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&otherKey={1234}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('{"result":{"phid": "'.$phid.'", "userName": "'.$userName.'", "realName": "'.$realName.'", "image": "'.$image.'", "uri": "'.$uri.'", "roles": ["'.implode('","', $roles).'"], "primaryEmail": "'.$primaryEmail.'"},"error_code": null, "error_info": null}');
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($phid, $user->getId());
        $this->assertEquals($phid, $user->getPhid());
        $this->assertEquals($phid, $user->toArray()['phid']);
        $this->assertEquals($userName, $user->getUsername());
        $this->assertEquals($userName, $user->toArray()['userName']);
        $this->assertEquals($realName, $user->getRealName());
        $this->assertEquals($realName, $user->toArray()['realName']);
        $this->assertEquals($image, $user->getImage());
        $this->assertEquals($image, $user->toArray()['image']);
        $this->assertEquals($uri, $user->getUri());
        $this->assertEquals($uri, $user->toArray()['uri']);
        $this->assertEquals($roles, $user->getRoles());
        $this->assertEquals($roles, $user->toArray()['roles']);
        $this->assertEquals($primaryEmail, $user->getPrimaryEmail());
        $this->assertEquals($primaryEmail, $user->toArray()['primaryEmail']);        
    }   

    /**
     * @expectedException Ofbeaton\OAuth2\Client\Provider\Exception\PhabricatorIdentityProviderException
     **/
    public function testExceptionThrownWhenClientErrorReceived()
    {
        $status = rand(400,600);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"error": "invalid_grant","error_description": "Invalid authorization code 3242sada2334asasdf231sda."}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    /**
     * @expectedException \Ofbeaton\OAuth2\Client\Provider\Exception\PhabricatorIdentityProviderException
     **/
    public function testExceptionThrownWhenOAuthErrorReceived()
    {
        $status = 200;
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"error": "invalid_grant","error_description": "Invalid authorization code 3242sada2334asasdf231sda."}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testExceptionThrownWhenNoDomainProvided()
    {
        $this->provider = new \Ofbeaton\OAuth2\Client\Provider\Phabricator([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);    
    }
}
