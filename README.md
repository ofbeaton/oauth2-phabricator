# Github Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/ofbeaton/oauth2-phabricator.svg?style=flat-square)](https://github.com/ofbeaton/oauth2-phabricator/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/ofbeaton/oauth2-phabricator/master.svg?style=flat-square)](https://travis-ci.org/ofbeaton/oauth2-phabricator)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/ofbeaton/oauth2-phabricator.svg?style=flat-square)](https://scrutinizer-ci.com/g/ofbeaton/oauth2-phabricator/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/ofbeaton/oauth2-phabricator.svg?style=flat-square)](https://scrutinizer-ci.com/g/ofbeaton/oauth2-phabricator)
[![Total Downloads](https://img.shields.io/packagist/dt/ofbeaton/oauth2-phabricator.svg?style=flat-square)](https://packagist.org/packages/ofbeaton/oauth2-phabricator)

This package provides [Phabricator](https://www.phacility.com/) OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require ofbeaton/oauth2-phabricator
```

## Usage

Usage is the same as The League's OAuth client, using `\Ofbeaton\OAuth2\Client\Provider\Phabricator` as the provider.

### Authorization Code Flow

```php
$provider = new Ofbeaton\OAuth2\Client\Provider\Phabricator([
    'clientId'          => '{phabricator-client-id}',
    'clientSecret'      => '{phabricator-client-secret}',
    'redirectUri'       => 'https://your-phabricator-install.com/callback-url',
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getNickname());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Managing Scopes

When creating your Phabricator authorization URL, you can specify the state and scopes your application may authorize.

```php
$options = [
    'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
    'scope' => ['user','user:email','repo'] // array or string
];

$authorizationUrl = $provider->getAuthorizationUrl($options);
```
If neither are defined, the provider will utilize internal defaults.

At the time of authoring this documentation, [no scopes are available](https://secure.phabricator.com/book/phabcontrib/article/using_oauthserver/).

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/ofbeaton/oauth2-phabricator/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Finlay Beaton](https://ofbeaton.com)
- [Steven Maguire](https://github.com/stevenmaguire)
- [All Contributors](https://github.com/ofbeaton/oauth2-phabricator/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/ofbeaton/oauth2-phabricator/blob/master/LICENSE) for more information.

## Support Me

Hi, I'm Finlay Beaton ([@ofbeaton](https://ofbeaton.com)). This software is only made possible by donations of fellow users like you, encouraging me to toil the midnight hours away and sweat into the code and documentation. Everyone's time should be valuable, please seriously consider donating.

[Paypal Donate Form](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=RDWQCGL5UD6DS&lc=CA&item_name=ofbeaton&item_number=oauth-phabricator&currency_code=CAD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted)