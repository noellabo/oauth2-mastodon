<?php

namespace Noellabo\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;

class Mastodon extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $appName;

    /**
     * @var string
     */
    private $scopes;

    /**
     * @var string
     */
    private $website;

    public function __construct(array $options = [], array $collaborators = [])
    {
        $this->assertRequiredOptions($options);

        $possible   = $this->getConfigurableOptions();
        $configured = array_intersect_key($options, array_flip($possible));

        foreach ($configured as $key => $value) {
            $this->$key = $value;
        }

        // Remove all options that are only used locally
        $options = array_diff_key($options, $configured);

        parent::__construct($options, $collaborators);

        if (empty($this->clientId)) {
            $this->registerApp();
        }
    }

    /**
     * Returns all options that can be configured.
     *
     * @return array
     */
    protected function getConfigurableOptions()
    {
        return array_merge($this->getRequiredOptions(), [
            'scopes',
            'website',
        ]);
    }

    /**
     * Returns all options that are required.
     *
     * @return array
     */
    protected function getRequiredOptions()
    {
        return [
            'domain',
            'appName',
        ];
    }

    /**
     * Verifies that all required options have been passed.
     *
     * @param  array $options
     * @return void
     * @throws Exception
     */
    protected function assertRequiredOptions(array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);

        if (!empty($missing)) {
            throw new \Exception(
                'Required options not defined: ' . implode(', ', array_keys($missing))
            );
        }
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->domain . '/oauth/authorize';
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
        return $this->domain . '/oauth/token';
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
        return $this->domain . '/api/v1/accounts/verify_credentials';
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * Mastodon requires a space-separated list.
     *
     * @return string
     */
    protected function getScopeSeparator()
    {
        return ' ';
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
        if (!empty($data['error'])) {
            $message = $data['error'].': '.$data['error_description'];
            throw new IdentityProviderException($message, $response->getStatusCode(), $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new MastodonResourceOwner(preg_replace('#^https://#', '', $this->domain), $response);
    }

    /**
     * Create OAuth Apps
     *
     * @return array
     */
    protected function registerApp()
    {
        if (! $this->domain || ! $this->appName || ! $this->redirectUri) {
            throw new \InvalidArgumentException();
        }
        $options = [
            'client_name'   => $this->appName,
            'redirect_uris' => $this->redirectUri,
            'scopes'        => $this->scopes ? $this->scopes : 'read write follow push',
        ];
        if ($this->website) {
            $options['website'] = $this->website;
        }
        $client = new Client([ 'base_uri' => $this->domain ]);
        $response = $client->request(
            'POST',
            '/api/v1/apps',
            [ 'form_params' => $options ]
        );
        $appInfo = json_decode($response->getBody()->getContents(), true);
        if (is_array($appInfo)) {
            $this->clientId     = $appInfo['client_id'];
            $this->clientSecret = $appInfo['client_secret'];
        }
    }

    /**
     * Get Regenerate Params
     *
     * Gets the constructor option for regenerating the provider.
     *
     * For example, you can duplicate the provider by passing the retrieved value to the constructor.
     *     $options = $old_provider->getRegenerateParams();
     *     $new_provider = new Mastodon($options);
     *
     * @return array
     */
    public function getRegenerateParams()
    {
        $param_keys = [
            'domain',
            'appName',
            'redirectUri',
            'scopes',
            'website',
            'clientId',
            'clientSecret',
        ];
        foreach ($param_keys as $key) {
            if (property_exists($this, $key)) {
                $params[$key] = $this->$key;
            }
        }
        return $params;
    }
}
