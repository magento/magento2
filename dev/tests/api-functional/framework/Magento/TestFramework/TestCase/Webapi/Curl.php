<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\TestFramework\TestCase\Webapi;

/**
 * A Curl client that can be called independently, outside of REST controller.
 *
 * Used by CookieManager tests.
 */
class Curl extends Adapter\Rest\CurlClient
{
    const COOKIE_HEADER = 'Set-Cookie: ';

    /**
     * @param string $resourcePath Resource URL like /V1/Resource1/123
     * @return string resource URL
     * @throws \Exception
     */
    public function constructResourceUrl($resourcePath)
    {
        return rtrim(TESTS_BASE_URL, '/') . ltrim($resourcePath, '/');
    }

    /**
     * Perform HTTP GET request
     *
     * @param string $resourcePath Resource URL like /V1/Resource1/123
     * @param array $data
     * @param array $headers
     * @return array
     */
    public function get($resourcePath, $data = [], $headers = [])
    {
        $url = $this->constructResourceUrl($resourcePath);
        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $curlOpts = [];
        $curlOpts[CURLOPT_CUSTOMREQUEST] = \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_GET;
        $curlOpts[CURLOPT_SSLVERSION] = 3;
        $response = $this->_invokeApi($url, $curlOpts, $headers);
        $response['cookies'] = $this->cookieParse($response['header']);
        return $response;
    }

    /**
     * Takes a string in the form of an HTTP header block, returns cookie data.
     *
     * Return array is in the form of:
     *  [
     *      [
     *          'name' = <cookie_name>,
     *          'value' = <cookie_value>,
     *          <cookie_metadata_name> => <cookie_metadata_value> || 'true'
     *      ],
     *  ]
     *
     * @param $headerBlock
     * @return array
     */
    private function cookieParse($headerBlock)
    {
        $header = explode("\r\n", $headerBlock);
        $cookies = [];
        foreach ($header as $line) {
            $line = trim($line);
            if (substr($line, 0, strlen(self::COOKIE_HEADER)) == self::COOKIE_HEADER) {
                $line = trim(substr($line, strlen(self::COOKIE_HEADER)));
                $cookieData = [];
                // Check if cookie contains attributes
                if (strpos($line, ';') === false) {
                    // no attributes, just name and value
                    list($cookieData['name'], $cookieData['value']) = explode('=', $line);
                } else {
                    // has attributes, must parse them out and loop through
                    list($nvPair, $cookieMetadata) = explode(';', $line, 2);
                    list($cookieData['name'], $cookieData['value']) = explode('=', $nvPair);
                    $rawCookieData = explode(';', $cookieMetadata);
                    foreach ($rawCookieData as $keyValuePairs) {
                        list($key, $value) = array_merge(explode('=', $keyValuePairs), ['true']);
                        $cookieData[strtolower(trim($key))] = trim($value);
                    }
                }
                $cookies[] = $cookieData;
            }
        }
        return $cookies;
    }
}
