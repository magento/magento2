<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// @codingStandardsIgnoreStart
namespace {

    $mockTranslateSetCookie = false;
    $mockTranslateHeader = false;
}

namespace Magento\Framework\Stdlib\Test\Unit\Cookie
{

    use Magento\Framework\App\Request\Http;
    use Magento\Framework\Exception\InputException;
    use Magento\Framework\HTTP\Header as HttpHeader;
    use Magento\Framework\Phrase;
    use Magento\Framework\Stdlib\Cookie\CookieMetadata;
    use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;
    use Magento\Framework\Stdlib\Cookie\CookieScopeInterface;
    use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
    use Magento\Framework\Stdlib\Cookie\FailureToSendException;
    use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
    use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
    use Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata;
    use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
    use PHPUnit\Framework\MockObject\MockObject;
    use PHPUnit\Framework\TestCase;
    use Psr\Log\LoggerInterface;

    // @codingStandardsIgnoreEnd

    /**
     * Test PhpCookieManager
     *
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    class PhpCookieManagerTest extends TestCase
    {
        const COOKIE_NAME = 'cookie_name';
        const SENSITIVE_COOKIE_NAME_NO_METADATA_HTTPS = 'sensitive_cookie_name_no_metadata_https';
        const SENSITIVE_COOKIE_NAME_NO_METADATA_NOT_HTTPS = 'sensitive_cookie_name_no_metadata_not_https';
        const SENSITIVE_COOKIE_NAME_NO_DOMAIN_NO_PATH = 'sensitive_cookie_name_no_domain_no_path';
        const SENSITIVE_COOKIE_NAME_WITH_DOMAIN_AND_PATH = 'sensitive_cookie_name_with_domain_and_path';
        const PUBLIC_COOKIE_NAME_NO_METADATA = 'public_cookie_name_no_metadata';
        const PUBLIC_COOKIE_NAME_DEFAULT_VALUES = 'public_cookie_name_default_values';
        const PUBLIC_COOKIE_NAME_SOME_FIELDS_SET = 'public_cookie_name_some_fields_set';
        const MAX_COOKIE_SIZE_TEST_NAME = 'max_cookie_size_test_name';
        const MAX_NUM_COOKIE_TEST_NAME = 'max_num_cookie_test_name';
        const DELETE_COOKIE_NAME = 'delete_cookie_name';
        const DELETE_COOKIE_NAME_NO_METADATA = 'delete_cookie_name_no_metadata';
        const EXCEPTION_COOKIE_NAME = 'exception_cookie_name';
        const COOKIE_VALUE = 'cookie_value';
        const DEFAULT_VAL = 'default';
        const COOKIE_SECURE = true;
        const COOKIE_NOT_SECURE = false;
        const COOKIE_HTTP_ONLY = true;
        const COOKIE_NOT_HTTP_ONLY = false;
        const COOKIE_EXPIRE_END_OF_SESSION = 0;

        /**
         * Mapping from constant names to functions that handle the assertions.
         */
        protected static $functionTestAssertionMapping = [
            self::DELETE_COOKIE_NAME => 'self::assertDeleteCookie',
            self::DELETE_COOKIE_NAME_NO_METADATA => 'self::assertDeleteCookieWithNoMetadata',
            self::SENSITIVE_COOKIE_NAME_NO_METADATA_HTTPS => 'self::assertSensitiveCookieWithNoMetaDataHttps',
            self::SENSITIVE_COOKIE_NAME_NO_METADATA_NOT_HTTPS => 'self::assertSensitiveCookieWithNoMetaDataNotHttps',
            self::SENSITIVE_COOKIE_NAME_NO_DOMAIN_NO_PATH => 'self::assertSensitiveCookieNoDomainNoPath',
            self::SENSITIVE_COOKIE_NAME_WITH_DOMAIN_AND_PATH => 'self::assertSensitiveCookieWithDomainAndPath',
            self::PUBLIC_COOKIE_NAME_NO_METADATA => 'self::assertPublicCookieWithNoMetaData',
            self::PUBLIC_COOKIE_NAME_DEFAULT_VALUES => 'self::assertPublicCookieWithDefaultValues',
            self::PUBLIC_COOKIE_NAME_SOME_FIELDS_SET => 'self::assertPublicCookieWithSomeFieldSet',
            self::MAX_COOKIE_SIZE_TEST_NAME => 'self::assertCookieSize',
        ];

        /**
         * @var ObjectManager
         */
        protected $objectManager;

        /**
         * Cookie Manager
         *
         * @var PhpCookieManager
         */
        protected $cookieManager;

        /**
         * @var MockObject|CookieScopeInterface
         */
        protected $scopeMock;

        /**
         * @var bool
         */
        public static $isSetCookieInvoked;

        /**
         * @var Http|MockObject
         */
        protected $requestMock;

        /**
         * @var CookieReaderInterface|MockObject
         */
        protected $readerMock;

        /**
         * @var LoggerInterface|MockObject
         */
        protected $loggerMock;

        /**
         * @var HttpHeader|MockObject
         */
        protected $httpHeaderMock;

        /**
         * @var array
         */
        protected $cookieArray;

        /**
         * @var array
         */
        public static $outputHeaders = [];

        protected function setUp(): void
        {
            require_once __DIR__ . '/_files/setcookie_mock.php';
            global $mockTranslateSetCookie, $mockTranslateHeader;
            $mockTranslateSetCookie = true;
            $mockTranslateHeader = true;
            $this->cookieArray = $_COOKIE;
            self::$isSetCookieInvoked = false;
            $this->objectManager = new ObjectManager($this);
            $this->scopeMock = $this->getMockBuilder(CookieScopeInterface::class)
                ->setMethods(['getPublicCookieMetadata', 'getCookieMetadata', 'getSensitiveCookieMetadata'])
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
            $this->readerMock = $this->getMockForAbstractClass(CookieReaderInterface::class);
            $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
                ->getMockForAbstractClass();
            $this->httpHeaderMock = $this->getMockBuilder(HttpHeader::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->cookieManager = $this->objectManager->getObject(
                PhpCookieManager::class,
                [
                    'scope' => $this->scopeMock,
                    'reader' => $this->readerMock,
                    'logger' => $this->loggerMock,
                    'httpHeader' => $this->httpHeaderMock
                ]
            );

            $this->requestMock = $this->getMockBuilder(Http::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        protected function tearDown(): void
        {
            global $mockTranslateSetCookie;
            global $mockTranslateHeader;
            $mockTranslateSetCookie = false;
            $mockTranslateHeader = false;
            $_COOKIE = $this->cookieArray = $_COOKIE;
        }

        public function testGetUnknownCookie()
        {
            $unknownCookieName = 'unknownCookieName';
            $this->stubGetCookie($unknownCookieName, self::DEFAULT_VAL, self::DEFAULT_VAL);
            $this->assertEquals(
                self::DEFAULT_VAL,
                $this->cookieManager->getCookie($unknownCookieName, self::DEFAULT_VAL)
            );
        }

        public function testGetCookie()
        {
            $this->stubGetCookie(self::COOKIE_NAME, self::DEFAULT_VAL, self::COOKIE_VALUE);
            $this->assertEquals(
                self::COOKIE_VALUE,
                $this->cookieManager->getCookie(self::COOKIE_NAME, self::DEFAULT_VAL)
            );
        }

        public function testDeleteCookie()
        {
            self::$isSetCookieInvoked = false;

            /** @var CookieMetadata $cookieMetadata */
            $cookieMetadata = $this->objectManager->getObject(
                CookieMetadata::class,
                [
                    'metadata' => [
                        'domain' => 'magento.url',
                        'path' => '/backend',
                        'samesite' => 'Strict'
                    ]
                ]
            );

            $this->scopeMock->expects($this->once())
                ->method('getCookieMetadata')
                ->with($cookieMetadata)
                ->willReturn(
                    $cookieMetadata
                );

            $this->cookieManager->deleteCookie(self::DELETE_COOKIE_NAME, $cookieMetadata);
            $this->assertTrue(self::$isSetCookieInvoked);
        }

        public function testDeleteCookieWithNoCookieMetadata()
        {
            self::$isSetCookieInvoked = false;

            $cookieMetadata = $this->objectManager->getObject(CookieMetadata::class);
            $this->scopeMock->expects($this->once())
                ->method('getCookieMetadata')
                ->with()
                ->willReturn(
                    $cookieMetadata
                );

            $this->cookieManager->deleteCookie(self::DELETE_COOKIE_NAME_NO_METADATA);
            $this->assertTrue(self::$isSetCookieInvoked);
        }

        public function testDeleteCookieWithFailureToSendException()
        {
            global $mockTranslateHeader;
            self::$isSetCookieInvoked = false;
            $mockTranslateHeader = false;
            $cookieMetadata = $this->objectManager->getObject(CookieMetadata::class);
            $this->scopeMock->expects($this->once())
                ->method('getCookieMetadata')
                ->with()
                ->willReturn(
                    $cookieMetadata
                );

            try {
                $this->cookieManager->deleteCookie(self::EXCEPTION_COOKIE_NAME, $cookieMetadata);
                $this->fail('Expected exception not thrown.');
            } catch (FailureToSendException $fse) {
                $this->assertTrue(self::$isSetCookieInvoked);
                $this->assertSame(
                    'The cookie with "exception_cookie_name" cookieName couldn\'t be deleted.',
                    $fse->getMessage()
                );
            }
        }

        /**
         * @param string $cookieName
         * @param bool $secure
         * @dataProvider isCurrentlySecureDataProvider
         */
        public function testSetSensitiveCookieNoMetadata($cookieName, $secure)
        {
            self::$isSetCookieInvoked = false;
            /** @var SensitiveCookieMetadata $sensitiveCookieMetadata */
            $sensitiveCookieMetadata = $this->objectManager
                ->getObject(
                    SensitiveCookieMetadata::class,
                    [
                        'request' => $this->requestMock
                    ]
                );
            $this->scopeMock->expects($this->once())
                ->method('getSensitiveCookieMetadata')
                ->with()
                ->willReturn(
                    $sensitiveCookieMetadata
                );

            $this->requestMock->expects($this->once())
                ->method('isSecure')
                ->willReturn($secure);

            $this->cookieManager->setSensitiveCookie(
                $cookieName,
                'cookie_value'
            );
            $this->assertTrue(self::$isSetCookieInvoked);
        }

        /**
         * @return array
         */
        public function isCurrentlySecureDataProvider()
        {
            return [
                [self::SENSITIVE_COOKIE_NAME_NO_METADATA_HTTPS, true],
                [self::SENSITIVE_COOKIE_NAME_NO_METADATA_NOT_HTTPS, false]
            ];
        }

        public function testSetSensitiveCookieNullDomainAndPath()
        {
            self::$isSetCookieInvoked = false;
            /** @var SensitiveCookieMetadata $sensitiveCookieMetadata */
            $sensitiveCookieMetadata = $this->objectManager
                ->getObject(
                    SensitiveCookieMetadata::class,
                    [
                        'request' => $this->requestMock,
                        'metadata' => [
                            'domain' => null,
                            'path' => null,
                        ],
                    ]
                );

            $this->scopeMock->expects($this->once())
                ->method('getSensitiveCookieMetadata')
                ->with($sensitiveCookieMetadata)
                ->willReturn(
                    $sensitiveCookieMetadata
                );

            $this->requestMock->expects($this->once())
                ->method('isSecure')
                ->willReturn(true);

            $this->cookieManager->setSensitiveCookie(
                self::SENSITIVE_COOKIE_NAME_NO_DOMAIN_NO_PATH,
                'cookie_value',
                $sensitiveCookieMetadata
            );
            $this->assertTrue(self::$isSetCookieInvoked);
        }

        public function testSetSensitiveCookieWithPathAndDomain()
        {
            self::$isSetCookieInvoked = false;
            /** @var SensitiveCookieMetadata $sensitiveCookieMetadata */
            $sensitiveCookieMetadata = $this->objectManager
                ->getObject(
                    SensitiveCookieMetadata::class,
                    [
                        'request' => $this->requestMock,
                        'metadata' => [
                            'domain' => 'magento.url',
                            'path' => '/backend',
                        ],
                    ]
                );

            $this->scopeMock->expects($this->once())
                ->method('getSensitiveCookieMetadata')
                ->with($sensitiveCookieMetadata)
                ->willReturn(
                    $sensitiveCookieMetadata
                );

            $this->requestMock->expects($this->once())
                ->method('isSecure')
                ->willReturn(false);

            $this->cookieManager->setSensitiveCookie(
                self::SENSITIVE_COOKIE_NAME_WITH_DOMAIN_AND_PATH,
                'cookie_value',
                $sensitiveCookieMetadata
            );
            $this->assertTrue(self::$isSetCookieInvoked);
        }

        public function testSetPublicCookieNoMetadata()
        {
            self::$isSetCookieInvoked = false;
            /** @var PublicCookieMetadata $publicCookieMetadata */
            $publicCookieMetadata = $this->objectManager->getObject(
                PublicCookieMetadata::class
            );

            $this->scopeMock->expects($this->once())
                ->method('getPublicCookieMetadata')
                ->with()
                ->willReturn(
                    $publicCookieMetadata
                );

            $this->cookieManager->setPublicCookie(
                self::PUBLIC_COOKIE_NAME_NO_METADATA,
                'cookie_value'
            );
            $this->assertTrue(self::$isSetCookieInvoked);
        }

        public function testSetPublicCookieDefaultValues()
        {
            /** @var PublicCookieMetadata $publicCookieMetadata */
            $publicCookieMetadata = $this->objectManager->getObject(
                PublicCookieMetadata::class,
                [
                    'metadata' => [
                        'domain' => null,
                        'path' => null,
                        'secure' => false,
                        'http_only' => false,
                    ],
                ]
            );

            $this->scopeMock->expects($this->once())
                ->method('getPublicCookieMetadata')
                ->with($publicCookieMetadata)
                ->willReturn(
                    $publicCookieMetadata
                );

            $this->cookieManager->setPublicCookie(
                self::PUBLIC_COOKIE_NAME_DEFAULT_VALUES,
                'cookie_value',
                $publicCookieMetadata
            );

            $this->assertTrue(self::$isSetCookieInvoked);
        }

        public function testSetPublicCookieSomeFieldsSet()
        {
            self::$isSetCookieInvoked = false;
            /** @var PublicCookieMetadata $publicCookieMetadata */
            $publicCookieMetadata = $this->objectManager->getObject(
                PublicCookieMetadata::class,
                [
                    'metadata' => [
                        'domain' => 'magento.url',
                        'path' => '/backend',
                        'http_only' => true,
                        'samesite' => 'Lax'
                    ],
                ]
            );

            $this->scopeMock->expects($this->once())
                ->method('getPublicCookieMetadata')
                ->with($publicCookieMetadata)
                ->willReturn(
                    $publicCookieMetadata
                );

            $this->cookieManager->setPublicCookie(
                self::PUBLIC_COOKIE_NAME_SOME_FIELDS_SET,
                'cookie_value',
                $publicCookieMetadata
            );
            $this->assertTrue(self::$isSetCookieInvoked);
        }

        public function testSetCookieBadName()
        {
            /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata $publicCookieMetadata */
            $publicCookieMetadata = $this->objectManager->getObject(
                PublicCookieMetadata::class,
                [
                    'metadata' => [
                        'domain' => null,
                        'path' => null,
                        'secure' => false,
                        'http_only' => false,
                    ],
                ]
            );

            $badCookieName = '';
            $cookieValue = 'some_value';

            $this->scopeMock->expects($this->once())
                ->method('getPublicCookieMetadata')
                ->with()
                ->willReturn(
                    $publicCookieMetadata
                );

            try {
                $this->cookieManager->setPublicCookie(
                    $badCookieName,
                    $cookieValue,
                    $publicCookieMetadata
                );
                $this->fail('Failed to throw exception of bad cookie name');
            } catch (InputException $e) {
                $this->assertEquals(
                    'Cookie name cannot be empty and cannot contain these characters: =,; \\t\\r\\n\\013\\014',
                    $e->getMessage()
                );
            }
        }

        public function testSetCookieSizeTooLarge()
        {
            /** @var PublicCookieMetadata $publicCookieMetadata */
            $publicCookieMetadata = $this->objectManager->getObject(
                PublicCookieMetadata::class,
                [
                    'metadata' => [
                        'domain' => null,
                        'path' => null,
                        'secure' => false,
                        'http_only' => false,
                        'duration' => 3600,
                        'samesite' => 'Strict'
                    ],
                ]
            );

            $this->scopeMock->expects($this->once())
                ->method('getPublicCookieMetadata')
                ->with()
                ->willReturn(
                    $publicCookieMetadata
                );

            $cookieValue = '';

            $cookieManager = $this->cookieManager;
            for ($i = 0; $i < $cookieManager::MAX_COOKIE_SIZE + 1; $i++) {
                $cookieValue = $cookieValue . 'a';
            }

            try {
                $this->cookieManager->setPublicCookie(
                    self::MAX_COOKIE_SIZE_TEST_NAME,
                    $cookieValue,
                    $publicCookieMetadata
                );
                $this->fail('Failed to throw exception of excess cookie size.');
            } catch (CookieSizeLimitReachedException $e) {
                $this->assertEquals(
                    "Unable to send the cookie. Size of 'max_cookie_size_test_name' is 4123 bytes.",
                    $e->getMessage()
                );
            }
        }

        public function testSetTooManyCookies()
        {
            /** @var PublicCookieMetadata $publicCookieMetadata */
            $publicCookieMetadata = $this->objectManager->getObject(
                PublicCookieMetadata::class
            );

            $userAgent = 'some_user_agent';

            $cookieManager = $this->cookieManager;
            // Set $cookieManager::MAX_NUM_COOKIES number of cookies in superglobal $_COOKIE.
            for ($i = count($_COOKIE); $i < $cookieManager::MAX_NUM_COOKIES; $i++) {
                $_COOKIE['test_cookie_' . $i] = self::COOKIE_VALUE . '_' . $i;
            }

            $this->scopeMock->expects($this->once())
                ->method('getPublicCookieMetadata')
                ->with()
                ->willReturn(
                    $publicCookieMetadata
                );

            $this->httpHeaderMock->expects($this->any())
                ->method('getHttpUserAgent')
                ->willReturn($userAgent);

            $this->loggerMock->expects($this->once())
                ->method('warning')
                ->with(
                    new Phrase('Unable to send the cookie. Maximum number of cookies would be exceeded.'),
                    array_merge($_COOKIE, ['user-agent' => $userAgent])
                );

            $this->cookieManager->setPublicCookie(
                self::MAX_COOKIE_SIZE_TEST_NAME,
                self::COOKIE_VALUE,
                $publicCookieMetadata
            );
        }

        /**
         * Assert public, sensitive and delete cookie
         *
         * Suppressing UnusedFormalParameter, since PHPMD doesn't detect the callback call.
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        public static function assertCookie($name, $value, $expiry, $path, $domain, $secure, $httpOnly, $sameSite)
        {
            if (self::EXCEPTION_COOKIE_NAME == $name) {
                return false;
            } elseif (isset(self::$functionTestAssertionMapping[$name])) {
                // phpcs:ignore
                call_user_func_array(self::$functionTestAssertionMapping[$name], func_get_args());
            } else {
                self::fail('Non-tested case in mock setcookie()');
            }
            return true;
        }

        /**
         * Assert delete cookie
         *
         * Suppressing UnusedPrivateMethod, since PHPMD doesn't detect callback method use.
         * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
         */
        private static function assertDeleteCookie(
            $name,
            $value,
            $expiry,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        ) {

            self::assertEquals(self::DELETE_COOKIE_NAME, $name);
            self::assertEquals('', $value);
            self::assertEquals($expiry, PhpCookieManager::EXPIRE_NOW_TIME);
            self::assertFalse($secure);
            self::assertFalse($httpOnly);
            self::assertEquals('magento.url', $domain);
            self::assertEquals('/backend', $path);
            self::assertEquals('Strict', $sameSite);
        }

        /**
         * Assert delete cookie with no meta data
         *
         * Suppressing UnusedPrivateMethod, since PHPMD doesn't detect callback method use.
         * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
         */
        private static function assertDeleteCookieWithNoMetadata(
            $name,
            $value,
            $expiry,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        ) {

            self::assertEquals(self::DELETE_COOKIE_NAME_NO_METADATA, $name);
            self::assertEquals('', $value);
            self::assertEquals($expiry, PhpCookieManager::EXPIRE_NOW_TIME);
            self::assertFalse($secure);
            self::assertFalse($httpOnly);
            self::assertEquals('', $domain);
            self::assertEquals('', $path);
            self::assertEquals('Lax', $sameSite);
        }

        /**
         * Assert sensitive cookie with no meta data
         *
         * Suppressing UnusedPrivateMethod, since PHPMD doesn't detect callback method use.
         * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
         */
        private static function assertSensitiveCookieWithNoMetaDataHttps(
            $name,
            $value,
            $expiry,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        ) {

            self::assertEquals(self::SENSITIVE_COOKIE_NAME_NO_METADATA_HTTPS, $name);
            self::assertEquals(self::COOKIE_VALUE, $value);
            self::assertEquals(PhpCookieManager::EXPIRE_AT_END_OF_SESSION_TIME, $expiry);
            self::assertTrue($secure);
            self::assertTrue($httpOnly);
            self::assertEquals('', $domain);
            self::assertEquals('', $path);
            self::assertEquals('Lax', $sameSite);
        }

        /**
         * Assert sensitive cookie with no meta data
         *
         * Suppressing UnusedPrivateMethod, since PHPMD doesn't detect callback method use.
         * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
         */
        private static function assertSensitiveCookieWithNoMetaDataNotHttps(
            $name,
            $value,
            $expiry,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        ) {
            self::assertEquals(self::SENSITIVE_COOKIE_NAME_NO_METADATA_NOT_HTTPS, $name);
            self::assertEquals(self::COOKIE_VALUE, $value);
            self::assertEquals(PhpCookieManager::EXPIRE_AT_END_OF_SESSION_TIME, $expiry);
            self::assertFalse($secure);
            self::assertTrue($httpOnly);
            self::assertEquals('', $domain);
            self::assertEquals('', $path);
            self::assertEquals('Lax', $sameSite);
        }

        /**
         * Assert sensitive cookie with no domain and path
         *
         * Suppressing UnusedPrivateMethod, since PHPMD doesn't detect callback method use.
         * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
         */
        private static function assertSensitiveCookieNoDomainNoPath(
            $name,
            $value,
            $expiry,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        ) {
            self::assertEquals(self::SENSITIVE_COOKIE_NAME_NO_DOMAIN_NO_PATH, $name);
            self::assertEquals(self::COOKIE_VALUE, $value);
            self::assertEquals(PhpCookieManager::EXPIRE_AT_END_OF_SESSION_TIME, $expiry);
            self::assertTrue($secure);
            self::assertTrue($httpOnly);
            self::assertEquals('', $domain);
            self::assertEquals('', $path);
            self::assertEquals('Lax', $sameSite);
        }

        /**
         * Assert sensitive cookie with domain and path
         *
         * Suppressing UnusedPrivateMethod, since PHPMD doesn't detect callback method use.
         * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
         */
        private static function assertSensitiveCookieWithDomainAndPath(
            $name,
            $value,
            $expiry,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        ) {
            self::assertEquals(self::SENSITIVE_COOKIE_NAME_WITH_DOMAIN_AND_PATH, $name);
            self::assertEquals(self::COOKIE_VALUE, $value);
            self::assertEquals(PhpCookieManager::EXPIRE_AT_END_OF_SESSION_TIME, $expiry);
            self::assertFalse($secure);
            self::assertTrue($httpOnly);
            self::assertEquals('magento.url', $domain);
            self::assertEquals('/backend', $path);
            self::assertEquals('Lax', $sameSite);
        }

        /**
         * Assert public cookie with no metadata
         *
         * Suppressing UnusedPrivateMethod, since PHPMD doesn't detect callback method use.
         * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
         */
        private static function assertPublicCookieWithNoMetaData(
            $name,
            $value,
            $expiry,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        ) {
            self::assertEquals(self::PUBLIC_COOKIE_NAME_NO_METADATA, $name);
            self::assertEquals(self::COOKIE_VALUE, $value);
            self::assertEquals(self::COOKIE_EXPIRE_END_OF_SESSION, $expiry);
            self::assertFalse($secure);
            self::assertFalse($httpOnly);
            self::assertEquals('', $domain);
            self::assertEquals('', $path);
            self::assertEquals('Lax', $sameSite);
        }

        /**
         * Assert public cookie with no domain and path
         *
         * Suppressing UnusedPrivateMethod, since PHPMD doesn't detect callback method use.
         * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
         */
        private static function assertPublicCookieWithNoDomainNoPath(
            $name,
            $value,
            $expiry,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        ) {
            self::assertEquals(self::PUBLIC_COOKIE_NAME_NO_METADATA, $name);
            self::assertEquals(self::COOKIE_VALUE, $value);
            self::assertEquals(PhpCookieManager::EXPIRE_AT_END_OF_SESSION_TIME, $expiry);
            self::assertTrue($secure);
            self::assertTrue($httpOnly);
            self::assertEquals('magento.url', $domain);
            self::assertEquals('/backend', $path);
            self::assertEquals('None', $sameSite);
        }

        /**
         * Assert public cookie with default values
         *
         * Suppressing UnusedPrivateMethod, since PHPMD doesn't detect callback method use.
         * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
         */
        private static function assertPublicCookieWithDefaultValues(
            $name,
            $value,
            $expiry,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        ) {
            self::assertEquals(self::PUBLIC_COOKIE_NAME_DEFAULT_VALUES, $name);
            self::assertEquals(self::COOKIE_VALUE, $value);
            self::assertEquals(self::COOKIE_EXPIRE_END_OF_SESSION, $expiry);
            self::assertFalse($secure);
            self::assertFalse($httpOnly);
            self::assertEquals('', $domain);
            self::assertEquals('', $path);
            self::assertEquals('Lax', $sameSite);
        }

        /**
         * Assert public cookie with no field set
         *
         * Suppressing UnusedPrivateMethod, since PHPMD doesn't detect callback method use.
         * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
         */
        private static function assertPublicCookieWithSomeFieldSet(
            $name,
            $value,
            $expiry,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        ) {
            self::assertEquals(self::PUBLIC_COOKIE_NAME_SOME_FIELDS_SET, $name);
            self::assertEquals(self::COOKIE_VALUE, $value);
            self::assertEquals(self::COOKIE_EXPIRE_END_OF_SESSION, $expiry);
            self::assertFalse($secure);
            self::assertTrue($httpOnly);
            self::assertEquals('magento.url', $domain);
            self::assertEquals('/backend', $path);
            self::assertEquals('/backend', $path);
            self::assertEquals('Lax', $sameSite);
        }

        /**
         * Assert cookie size
         *
         * Suppressing UnusedPrivateMethod, since PHPMD doesn't detect callback method use.
         * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
         */
        private static function assertCookieSize(
            $name,
            $value,
            $expiry,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        ) {
            self::assertEquals(self::MAX_COOKIE_SIZE_TEST_NAME, $name);
            self::assertEquals(self::COOKIE_VALUE, $value);
            self::assertEquals(self::COOKIE_EXPIRE_END_OF_SESSION, $expiry);
            self::assertFalse($secure);
            self::assertFalse($httpOnly);
            self::assertEquals('', $domain);
            self::assertEquals('', $path);
            self::assertEquals('Lax', $sameSite);
        }

        /**
         * @param $get
         * @param $default
         * @param $return
         */
        protected function stubGetCookie($get, $default, $return)
        {
            $this->readerMock->expects($this->atLeastOnce())
                ->method('getCookie')
                ->with($get, $default)
                ->willReturn($return);
        }

        /**
         * Test Set Invalid Same Site Cookie
         *
         * @return void
         */
        public function testSetCookieInvalidSameSiteValue(): void
        {
            /** @var \Magento\Framework\Stdlib\Cookie\CookieMetadata $cookieMetadata */
            $cookieMetadata = $this->objectManager->getObject(
                CookieMetadata::class
            );
            $this->expectException('InvalidArgumentException');
            $exceptionMessage = 'Invalid argument provided for SameSite directive expected one of: Strict, Lax or None';
            $this->expectExceptionMessage($exceptionMessage);
            $cookieMetadata->setSameSite('default value');
        }

        /**
         * Test Set SameSite None with Insecure cookies
         *
         * @return void
         */
        public function testSetSameSiteNone(): void
        {
            /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata $cookieMetadata */
            $cookieMetadata = $this->objectManager->getObject(
                CookieMetadata::class
            );
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionMessage('Cookie must be secure in order to use the SameSite None directive.');
            $cookieMetadata->setSameSite('None');
            $cookieMetadata->setSecure(false);
        }
    }
}
