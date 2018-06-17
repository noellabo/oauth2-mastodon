# Mastodon provider for OAuth 2.0 Client

This package provides Mastodon OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## 

## Installation

To install, use composer:

```
composer require noellabo/oauth2-mastodon
```

## Usage

Usage is the same as The League's OAuth client, using `\Noellabo\OAuth2\Client\Provider\Mastodon` as the provider.

In the case of ｍastodon, distributed instances have their own OAuth servers, so you need to authenticate by specifying the domain name.

oauth2-mastodon can register the application automatically by specifying the domain name. Once you register the information, please cache it and reuse it.

### Authorization Code Flow

```php
<?php

require_once './vendor/autoload.php';
use Noellabo\OAuth2\Client\Provider\Mastodon;

session_start();

// Mastodon instance url
$domain = 'https://example.com';

// Save the provider information for each instance and restore it
$instances_filename = 'instances.json';
$instances = json_decode(file_get_contents($instances_filename), true);
if (is_array($instances) && is_array($instances[ $domain ])) {
    $options = $instances[ $domain ];
}

// Set required parameters.
$options['domain']      = $domain;
$options['appName']     = 'OAuth2-Mastodon test app';
$options['redirectUri'] = 'http://localhost:3000/';

// Application registration is done through the API, so various exceptions are thrown. Properly deal with it.
$provider = new Mastodon($options);

// Acquire and save parameters for regenerating the provider. Save only the credential at a minimum.
$params = $provider->getRegenerateParams();
if (! empty($params['clientId'])) {
    $instances[ $domain ] = [
        'clientId'     => $params['clientId'],
        'clientSecret' => $params['clientSecret'],
    ];
}
file_put_contents($instances_filename, json_encode($instances, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

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

        // Use this to interact with an API on the users behalf
        printf("Authorization: Bearer %s\n", $token->getToken());

        // Use these details to create a new profile
        printf('<pre>');
        print_r($user->toArray());
        printf('</pre>');

        // Get '@username@domain' format
        printf('accr: %s', $user->getAcct());
    } catch (Exception $e) {
        // Failed to get user details
        exit('Oh dear...');
    }
}

```

## Testing

``` bash
$ ./vendor/bin/phpunit
```
## Credits

- [Takeshi Umeda](https://github.com/noellabo)

## License

The MIT License (MIT). Please see [License File](https://github.com/noellabo/oauth2-mastodon/blob/master/LICENSE) for more information.

