<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\HTTP\Header as HttpHeader;
use Psr\Log\LoggerInterface;

/**
 * CookieManager helps manage the setting, retrieving and deleting of cookies.
 *
 * To aid in security, the cookie manager will make it possible for the application to indicate if the cookie contains
 * sensitive data so that extra protection can be added to the contents of the cookie as well as how the browser
 * stores the cookie.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PhpCookieManager implements CookieManagerInterface
{
    /**#@+
     * Constants for Cookie manager.
     * RFC 2109 - Page 15
     * http://www.ietf.org/rfc/rfc6265.txt
     */
    const MAX_NUM_COOKIES = 50;
    const MAX_COOKIE_SIZE = 4096;
    const EXPIRE_NOW_TIME = 1;
    const EXPIRE_AT_END_OF_SESSION_TIME = 0;
    /**#@-*/

    /**#@+
     * Constant for metadata array key
     */
    const KEY_EXPIRE_TIME = 'expiry';
    /**#@-*/

    /**#@-*/
    private $scope;

    /**
     * @var CookieReaderInterface
     */
    private $reader;

    /**
     * Logger for warning details.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Object that provides access to HTTP headers.
     *
     * @var HttpHeader
     */
    private $httpHeader;

    /**
     * @param CookieScopeInterface $scope
     * @param CookieReaderInterface $reader
     * @param LoggerInterface $logger
     * @param HttpHeader $httpHeader
     */
    public function __construct(
        CookieScopeInterface $scope,
        CookieReaderInterface $reader,
        LoggerInterface $logger = null,
        HttpHeader $httpHeader = null
    ) {
        $this->scope = $scope;
        $this->reader = $reader;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->httpHeader = $httpHeader ?: ObjectManager::getInstance()->get(HttpHeader::class);
    }

    /**
     * Set a value in a private cookie with the given $name $value pairing.
     *
     * Sensitive cookies cannot be accessed by JS. HttpOnly will always be set to true for these cookies.
     *
     * @param string $name
     * @param string $value
     * @param SensitiveCookieMetadata $metadata
     * @return void
     * @throws FailureToSendException Cookie couldn't be sent to the browser.  If this exception isn't thrown,
     * there is still no guarantee that the browser received and accepted the cookie.
     * @throws CookieSizeLimitReachedException Thrown when the cookie is too big to store any additional data.
     * @throws InputException If the cookie name is empty or contains invalid characters.
     */
    public function setSensitiveCookie($name, $value, SensitiveCookieMetadata $metadata = null)
    {
        $metadataArray = $this->scope->getSensitiveCookieMetadata($metadata)->__toArray();
        $this->setCookie($name, $value, $metadataArray);
    }

    /**
     * Set a value in a public cookie with the given $name $value pairing.
     *
     * Public cookies can be accessed by JS. HttpOnly will be set to false by default for these cookies,
     * but can be changed to true.
     *
     * @param string $name
     * @param string $value
     * @param PublicCookieMetadata $metadata
     * @return void
     * @throws FailureToSendException If cookie couldn't be sent to the browser.
     * @throws CookieSizeLimitReachedException Thrown when the cookie is too big to store any additional data.
     * @throws InputException If the cookie name is empty or contains invalid characters.
     */
    public function setPublicCookie($name, $value, PublicCookieMetadata $metadata = null)
    {
        $metadataArray = $this->scope->getPublicCookieMetadata($metadata)->__toArray();
        $this->setCookie($name, $value, $metadataArray);
    }

    /**
     * Set a value in a cookie with the given $name $value pairing.
     *
     * @param string $name
     * @param string $value
     * @param array $metadataArray
     * @return void
     * @throws FailureToSendException If cookie couldn't be sent to the browser.
     * @throws CookieSizeLimitReachedException Thrown when the cookie is too big to store any additional data.
     * @throws InputException If the cookie name is empty or contains invalid characters.
     */
    protected function setCookie($name, $value, array $metadataArray)
    {
        $expire = $this->computeExpirationTime($metadataArray);

        $this->checkAbilityToSendCookie($name, $value);

        $phpSetcookieSuccess = setcookie(
            $name,
            $value,
            $expire,
            $this->extractValue(CookieMetadata::KEY_PATH, $metadataArray, ''),
            $this->extractValue(CookieMetadata::KEY_DOMAIN, $metadataArray, ''),
            $this->extractValue(CookieMetadata::KEY_SECURE, $metadataArray, false),
            $this->extractValue(CookieMetadata::KEY_HTTP_ONLY, $metadataArray, false)
        );

        if (!$phpSetcookieSuccess) {
            $params['name'] = $name;
            if ($value == '') {
                throw new FailureToSendException(
                    new Phrase('Unable to delete the cookie with cookieName = %name', $params)
                );
            } else {
                throw new FailureToSendException(
                    new Phrase('Unable to send the cookie with cookieName = %name', $params)
                );
            }
        }
    }

    /**
     * Retrieve the size of a cookie.
     * The size of a cookie is determined by the length of 'name=value' portion of the cookie.
     *
     * @param string $name
     * @param string $value
     * @return int
     */
    private function sizeOfCookie($name, $value)
    {
        // The constant '1' is the length of the equal sign in 'name=value'.
        return strlen($name) + 1 + strlen($value);
    }

    /**
     * Determines whether or not it is possible to send the cookie, based on the number of cookies that already
     * exist and the size of the cookie.
     *
     * @param string $name
     * @param string|null $value
     * @return void if it is possible to send the cookie
     * @throws CookieSizeLimitReachedException Thrown when the cookie is too big to store any additional data.
     * @throws InputException If the cookie name is empty or contains invalid characters.
     */
    private function checkAbilityToSendCookie($name, $value)
    {
        if ($name == '' || preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new InputException(
                new Phrase(
                    'Cookie name cannot be empty and cannot contain these characters: =,; \\t\\r\\n\\013\\014'
                )
            );
        }

        $numCookies = count($_COOKIE);

        if (!isset($_COOKIE[$name])) {
            $numCookies++;
        }

        $sizeOfCookie = $this->sizeOfCookie($name, $value);

        if ($numCookies > PhpCookieManager::MAX_NUM_COOKIES) {
            $this->logger->warning(
                new Phrase('Unable to send the cookie. Maximum number of cookies would be exceeded.'),
                array_merge($_COOKIE, ['user-agent' => $this->httpHeader->getHttpUserAgent()])
            );
        }

        if ($sizeOfCookie > PhpCookieManager::MAX_COOKIE_SIZE) {
            throw new CookieSizeLimitReachedException(
                new Phrase(
                    'Unable to send the cookie. Size of \'%name\' is %size bytes.',
                    [
                        'name' => $name,
                        'size' => $sizeOfCookie,
                    ]
                )
            );
        }
    }

    /**
     * Determines the expiration time of a cookie.
     *
     * @param array $metadataArray
     * @return int in seconds since the Unix epoch.
     */
    private function computeExpirationTime(array $metadataArray)
    {
        if (isset($metadataArray[PhpCookieManager::KEY_EXPIRE_TIME])
            && $metadataArray[PhpCookieManager::KEY_EXPIRE_TIME] < time()
        ) {
            $expireTime = $metadataArray[PhpCookieManager::KEY_EXPIRE_TIME];
        } else {
            if (isset($metadataArray[CookieMetadata::KEY_DURATION])
                && $metadataArray[CookieMetadata::KEY_DURATION] !== PhpCookieManager::EXPIRE_AT_END_OF_SESSION_TIME
            ) {
                $expireTime = $metadataArray[CookieMetadata::KEY_DURATION] + time();
            } else {
                $expireTime = PhpCookieManager::EXPIRE_AT_END_OF_SESSION_TIME;
            }
        }

        return $expireTime;
    }

    /**
     * Determines the value to be used as a $parameter.
     * If $metadataArray[$parameter] is not set, returns the $defaultValue.
     *
     * @param string $parameter
     * @param array $metadataArray
     * @param string|boolean|int|null $defaultValue
     * @return string|boolean|int|null
     */
    private function extractValue($parameter, array $metadataArray, $defaultValue)
    {
        if (array_key_exists($parameter, $metadataArray)) {
            return $metadataArray[$parameter];
        } else {
            return $defaultValue;
        }
    }

    /**
     * Retrieve a value from a cookie.
     *
     * @param string $name
     * @param string|null $default The default value to return if no value could be found for the given $name.
     * @return string|null
     */
    public function getCookie($name, $default = null)
    {
        return $this->reader->getCookie($name, $default);
    }

    /**
     * Deletes a cookie with the given name.
     *
     * @param string $name
     * @param CookieMetadata $metadata
     * @return void
     * @throws FailureToSendException If cookie couldn't be sent to the browser.
     *     If this exception isn't thrown, there is still no guarantee that the browser
     *     received and accepted the request to delete this cookie.
     * @throws InputException If the cookie name is empty or contains invalid characters.
     */
    public function deleteCookie($name, CookieMetadata $metadata = null)
    {
        $metadataArray = $this->scope->getCookieMetadata($metadata)->__toArray();

        // explicitly set an expiration time in the metadataArray.
        $metadataArray[PhpCookieManager::KEY_EXPIRE_TIME] = PhpCookieManager::EXPIRE_NOW_TIME;

        $this->checkAbilityToSendCookie($name, '');

        // cookie value set to empty string to delete from the remote client
        $this->setCookie($name, '', $metadataArray);

        // Remove the cookie
        unset($_COOKIE[$name]);
    }
}
