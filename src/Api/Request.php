<?php

namespace seregazhuk\PinterestBot\Api;

use seregazhuk\PinterestBot\Contracts\HttpInterface;
use seregazhuk\PinterestBot\Contracts\RequestInterface;
use seregazhuk\PinterestBot\Helpers\CsrfHelper;
use seregazhuk\PinterestBot\Helpers\UrlHelper;

/**
 * Class Request.
 *
 * @property resource $ch
 * @property bool     $loggedIn
 * @property string   $userAgent
 * @property string   $csrfToken
 * @property string   $cookieJar
 */
class Request implements RequestInterface
{
    const INTEREST_ENTITY_ID = 'interest_id';
    const BOARD_ENTITY_ID = 'board_id';
    const COOKIE_NAME = 'pinterest_cookie';
    const PINNER_ENTITY_ID = 'user_id';

    protected $userAgent = 'Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0';
    /**
     * @var HttpInterface
     */
    protected $http;
    protected $loggedIn;
    protected $cookieJar;
    protected $options;

    public $csrfToken = '';

    /**
     * Common headers needed for every query.
     *
     * @var array
     */
    protected $requestHeaders = [
        'Accept: application/json, text/javascript, */*; q=0.01',
        'Accept-Language: en-US,en;q=0.5',
        'DNT: 1',
        'Host: nl.pinterest.com',
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'X-Pinterest-AppState: active',
        'X-NEW-APP: 1',
        'X-APP-VERSION: 04cf8cc',
        'X-Requested-With: XMLHttpRequest',
    ];

    /**
     * @param HttpInterface $http
     * @param string|null   $userAgent
     */
    public function __construct(HttpInterface $http, $userAgent = null)
    {
        $this->http = $http;
        if ($userAgent !== null) {
            $this->userAgent = $userAgent;
        }
        $this->cookieJar = self::COOKIE_NAME;
    }

    /**
     * Executes api call for follow or unfollow user.
     *
     * @param int    $entityId
     * @param string $entityName
     * @param string $url
     *
     * @return array
     */
    public function followMethodCall($entityId, $entityName, $url)
    {
        $dataJson = [
            'options' => [
                $entityName => $entityId,
            ],
            'context' => [],
        ];

        if ($entityName == self::INTEREST_ENTITY_ID) {
            $dataJson['options']['interest_list'] = 'favorited';
        }

        $post = ['data' => json_encode($dataJson, JSON_FORCE_OBJECT)];
        $postString = UrlHelper::buildRequestString($post);

        return $this->exec($url, $postString);
    }

    /**
     * Executes request to Pinterest API.
     *
     * @param string $resourceUrl
     * @param string $postString
     *
     * @return array
     */
    public function exec($resourceUrl, $postString = '')
    {
        $url = UrlHelper::buildApiUrl($resourceUrl);
        $this->makeHttpOptions($postString);
        $res = $this->http->execute($url, $this->options);

        return json_decode($res, true);
    }

    /**
     * Adds necessary curl options for query.
     *
     * @param string $postString POST query string
     *
     * @return $this
     */
    protected function makeHttpOptions($postString = '')
    {
        $this->setDefaultHttpOptions();

        if ($this->csrfToken == CsrfHelper::DEFAULT_TOKEN) {
            $this->options = $this->addDefaultCsrfInfo($this->options);
        }

        if (!empty($postString)) {
            $this->options[CURLOPT_POST] = true;
            $this->options[CURLOPT_POSTFIELDS] = $postString;
        }

        return $this;
    }

    /**
     * Clear token information.
     *
     * @return $this
     */
    public function clearToken()
    {
        $this->csrfToken = CsrfHelper::DEFAULT_TOKEN;

        return $this;
    }

    /**
     * Mark api as logged.
     *
     * @return $this
     */
    public function setLoggedIn()
    {
        $this->csrfToken = CsrfHelper::getTokenFromFile($this->cookieJar);
        if (!empty($this->csrfToken)) {
            $this->loggedIn = true;
        }

        return $this;
    }

    /**
     * Get log status.
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->loggedIn;
    }

    /**
     * Create request string.
     *
     * @param array  $data
     * @param string $sourceUrl
     * @param array  $bookmarks
     *
     * @return string
     */
    public static function createQuery(array $data = [], $sourceUrl = '/', $bookmarks = [])
    {
        $request = self::createRequestData($data, $sourceUrl, $bookmarks);

        return UrlHelper::buildRequestString($request);
    }

    /**
     * @param array|object $data
     * @param string|null  $sourceUrl
     * @param array        $bookmarks
     *
     * @return array
     */
    public static function createRequestData(array $data = [], $sourceUrl = '/', $bookmarks = [])
    {
        if (empty($data)) {
            $data = self::createEmptyRequestData();
        }

        if (!empty($bookmarks)) {
            $data['options']['bookmarks'] = $bookmarks;
        }

        $data['context'] = new \stdClass();

        return [
            'source_url' => $sourceUrl,
            'data'       => json_encode($data),
        ];
    }

    /**
     * @return array
     */
    protected static function createEmptyRequestData()
    {
        return ['options' => []];
    }

    /**
     * @return array
     */
    protected function setDefaultHttpOptions()
    {
        $this->options = [
            CURLOPT_USERAGENT      => $this->userAgent,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => 'gzip,deflate',
            CURLOPT_HTTPHEADER     => $this->getDefaultHttpHeaders(),
            CURLOPT_REFERER        => UrlHelper::URL_BASE,
            CURLOPT_COOKIEFILE     => $this->cookieJar,
            CURLOPT_COOKIEJAR      => $this->cookieJar,
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultHttpHeaders()
    {
        return array_merge($this->requestHeaders, ['X-CSRFToken: '.$this->csrfToken]);
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    protected function addDefaultCsrfInfo($options)
    {
        $options[CURLOPT_REFERER] = UrlHelper::URL_BASE;
        $options[CURLOPT_HTTPHEADER][] = CsrfHelper::getDefaultCookie();

        return $options;
    }
}
