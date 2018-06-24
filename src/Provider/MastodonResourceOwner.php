<?php

namespace Noellabo\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class MastodonResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * domain name
     *
     * @var array
     */
    protected $domain;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct($domain, array $response = array())
    {
        $this->domain   = $domain;
        $this->response = $response;
    }

    /**
     * Get resource owner id
     *
     * @return string|null
     */
    public function getId()
    {
        //vardump($this->response);die();
        return $this->getValueByKey($this->response, 'id');
    }

    /**
     * Get resource owner email (pseudo)
     *
     * @return string|null
     */
    public function getPseudoEmail()
    {
        return $this->getValueByKey($this->response, 'acct') . '@' . $this->domain . '.invalid';
    }

    /**
     * Get resource owner username
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->getValueByKey($this->response, 'username');
    }

    /**
     * Get resource owner acct
     *
     * @return string|null
     */
    public function getAcct()
    {
        return '@' . $this->getValueByKey($this->response, 'acct') . '@' . $this->domain;
    }

    /**
     * Get resource owner display name
     *
     * @return string|null
     */
    public function getDisplayname()
    {
        return $this->getValueByKey($this->response, 'display_name');
    }

    /**
     * Get resource owner link
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->getValueByKey($this->response, 'url');
    }

    /**
     * Get resource owner avatar url
     *
     * @return string|null
     */
    public function getAvatarUrl()
    {
        return $this->getValueByKey($this->response, 'avatar');
    }

    /**
     * Get resource owner static avatar image (gif) url
     *
     * @return string|null
     */
    public function getStaticAvatarUrl()
    {
        return $this->getValueByKey($this->response, 'avatar_static');
    }

    /**
     * Get resource owner snote
     *
     * @return string|null
     */
    public function getNote()
    {
        return $this->getValueByKey($this->response, 'note');
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->response, [
            'fullacct' => $this->getAcct(),
            'pseudo_email' => $this->getPseudoEmail(),
        ]);
    }
}
