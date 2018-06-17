<?php

namespace Noellabo\OAuth2\Client\Test\Provider;

use Noellabo\OAuth2\Client\Provider\Mastodon;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;

class MastodonTest extends TestCase
{
    protected $provider;

    public function argumentProvider()
    {
        return [
            [
                'options' => [
                    'domain'      => '',
                    'appName'     => 'OAuth2-Mastodon test app',
                    'redirectUri' => 'http://localhost:3000',
                    'scopes'      => 'read write follow push',
                    'website'     => 'https://github.com/noellabo/oauth-mastodon',
                ]
            ],
            [
                'options' => [
                    'domain'      => 'https://dtp-mstdn.jp',
                    'appName'     => '',
                    'redirectUri' => 'http://localhost:3000',
                    'scopes'      => 'read write follow push',
                    'website'     => 'https://github.com/noellabo/oauth-mastodon',
                ]
            ],
            [
                'options' => [
                    'domain'      => 'https://dtp-mstdn.jp',
                    'appName'     => 'OAuth2-Mastodon test app',
                    'redirectUri' => '',
                    'scopes'      => 'read write follow push',
                    'website'     => 'https://github.com/noellabo/oauth-mastodon',
                ]
            ],
        ];
    }

    /**
     * @dataProvider argumentProvider
     */
    public function testRegisterAppInvalidArgumentException($options)
    {
        $this->expectException(\InvalidArgumentException::class);

        $provider = new Mastodon($options);
    }

    public function invalidRequestProvider()
    {
        return [
            [
                'options' => [
                    'domain'      => 'http://dtp-mstdn.jp',
                    'appName'     => 'OAuth2-Mastodon test app',
                    'redirectUri' => 'http://localhost:3000',
                    'scopes'      => 'read write follow push',
                    'website'     => 'https://github.com/noellabo/oauth-mastodon',
                ]
            ],
            [
                'options' => [
                    'domain'      => 'https://dtp-discourse.jp',
                    'appName'     => 'OAuth2-Mastodon test app',
                    'redirectUri' => 'http://localhost:3000',
                    'scopes'      => 'read write follow push',
                    'website'     => 'https://github.com/noellabo/oauth-mastodon',
                ]
            ],
        ];
    }

    /**
     * @dataProvider invalidRequestProvider
     */
    public function testRegisterAppRequestException($options)
    {
        $this->expectException(RequestException::class);

        $provider = new Mastodon($options);
    }

    public function testRegisterApp()
    {
        $options = [
            'domain'      => 'https://dtp-mstdn.jp',
            'appName'     => 'OAuth2-Mastodon test app',
            'redirectUri' => 'http://localhost:3000',
            'scopes'      => 'read write follow push',
            'website'     => 'https://github.com/noellabo/oauth-mastodon',
        ];

        $provider = new Mastodon($options);
        $return_options = $provider->getRegenerateParams();

        $this->assertSame($options['domain'], $return_options['domain']);
        $this->assertSame($options['appName'], $return_options['appName']);
        $this->assertSame($options['redirectUri'], $return_options['redirectUri']);
        $this->assertSame($options['scopes'], $return_options['scopes']);
        $this->assertSame($options['website'], $return_options['website']);
        $this->assertArrayHasKey('clientId', $return_options);
        $this->assertArrayHasKey('clientSecret', $return_options);

        $this->provider = new Mastodon($return_options);
        $return_options2 = $this->provider->getRegenerateParams();

        $this->assertSame($return_options['clientId'], $return_options2['clientId']);
        $this->assertSame($return_options['clientSecret'], $return_options2['clientSecret']);
    }
}
