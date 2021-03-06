<?php

namespace seregazhuk\PinterestBot\Contracts;

interface ResponseInterface
{
    /**
     * Check if specified data exists in response.
     *
     * @param array|null $response
     * @param null       $key
     *
     * @return array|bool
     */
    public function getData($response, $key = null);

    /**
     * Check for error info in api response and save
     * it.
     *
     * @param array $response
     *
     * @return bool
     */
    public function checkErrorInResponse($response);

    /**
     * Checks if response is not empty.
     *
     * @param array $res
     *
     * @return bool
     */
    public function notEmpty($res);

    /**
     * Parse bookmarks from response.
     *
     * @param array $response
     *
     * @return array|null
     */
    public function getBookmarksFromResponse($response);

    /**
     * Checks Pinterest API paginated response, and parses data
     * with bookmarks info from it.
     *
     * @param array $res
     *
     * @return array
     */
    public function getPaginationData($res);

    /**
     * Checks if response is empty or has errors.
     *
     * @param $response
     *
     * @return mixed
     */
    public function checkResponse($response);

    /**
     * Parses Pinterest search API response for data and bookmarks
     * for next pagination page.
     *
     * @param array $response
     * @param bool  $bookmarksUsed
     *
     * @return array|null
     */
    public function parseSearchResponse($response, $bookmarksUsed = true);

    /**
     * Returns last error in response.
     *
     * @return array
     */
    public function getLastError();
}
