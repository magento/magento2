<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Framework\Session\Config
 */

namespace Magento\Framework\Session\Test\Unit;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Session\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\ValidatorInterface;
use Magento\Framework\ValidatorFactory;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Session\Config
     */
    protected $config;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var ValidatorFactory|MockObject
     */
    protected $validatorFactoryMock;

    /**
     * @var ValidatorInterface|MockObject
     */
    protected $validatorMock;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    protected function setUp(): void
    {
        $this->helper = new ObjectManager($this);

        $this->validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(true);
    }

    public function testSetOptionsInvalidValue()
    {
        $this->getModel($this->validatorMock);
        $preVal = $this->config->getOptions();
        $this->config->setOptions('');
        $this->assertEquals($preVal, $this->config->getOptions());
    }

    /**
     * @dataProvider optionsProvider
     */
    public function testSetOptions($option, $getter, $value)
    {
        $this->getModel($this->validatorMock);
        $options = [$option => $value];
        $this->config->setOptions($options);
        $this->assertSame($value, $this->config->{$getter}());
    }

    /**
     * @return array
     */
    public static function optionsProvider()
    {
        return [
            ['save_path', 'getSavePath', __DIR__],
            ['name', 'getName', 'FOOBAR'],
            ['gc_probability', 'getGcProbability', 42],
            ['gc_divisor', 'getGcDivisor', 3],
            ['gc_maxlifetime', 'getGcMaxlifetime', 180],
            ['serialize_handler', 'getSerializeHandler', 'php_binary'],
            ['cookie_lifetime', 'getCookieLifetime', 180],
            ['cookie_path', 'getCookiePath', '/foo/bar'],
            ['cookie_domain', 'getCookieDomain', 'framework.zend.com'],
            ['cookie_secure', 'getCookieSecure', true],
            ['cookie_httponly', 'getCookieHttpOnly', true],
            ['use_cookies', 'getUseCookies', false],
            ['use_only_cookies', 'getUseOnlyCookies', true],
            ['referer_check', 'getRefererCheck', 'foobar'],
            ['entropy_file', 'getEntropyFile', __FILE__],
            ['entropy_length', 'getEntropyLength', 42],
            ['cache_limiter', 'getCacheLimiter', 'private'],
            ['cache_expire', 'getCacheExpire', 42],
            ['use_trans_sid', 'getUseTransSid', true],
            ['hash_function', 'getHashFunction', 'md5'],
            ['hash_bits_per_character', 'getHashBitsPerCharacter', 5],
            ['url_rewriter_tags', 'getUrlRewriterTags', 'a=href'],
            ['cookie_samesite', 'getCookieSameSite', 'Lax']
        ];
    }

    public function testGetOptions()
    {
        $this->getModel($this->validatorMock);
        $appStateProperty = new \ReflectionProperty(Config::class, 'options');
        $appStateProperty->setAccessible(true);
        $original = $appStateProperty->getValue($this->config);
        $valueForTest = ['test' => 'test2'];
        $appStateProperty->setValue($this->config, $valueForTest);
        $this->assertEquals($valueForTest, $this->config->getOptions());
        $this->assertEquals($valueForTest, $this->config->toArray());
        $appStateProperty->setValue($this->config, $original);
        $this->assertEquals($original, $this->config->getOptions());
        $this->assertEquals($original, $this->config->toArray());
    }

    public function testNameIsMutable()
    {
        $this->getModel($this->validatorMock);
        $this->config->setName('FOOBAR');
        $this->assertEquals('FOOBAR', $this->config->getName());
    }

    public function testCookieLifetimeIsMutable()
    {
        $this->getModel($this->validatorMock);
        $this->config->setCookieLifetime(20);
        $this->assertEquals(20, $this->config->getCookieLifetime());
    }

    public function testCookieLifetimeCanBeZero()
    {
        $this->getModel($this->validatorMock);
        $this->config->setCookieLifetime(0);
        $this->assertEquals(0, ini_get('session.cookie_lifetime'));
    }

    public function testSettingInvalidCookieLifetime()
    {
        $returnMap =
            [
                ['foobar_bogus', false],
                ['Lax', true]
            ];
        $validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturnMap($returnMap);
        $this->getModel($validatorMock);
        $preVal = $this->config->getCookieLifetime();
        $this->config->setCookieLifetime('foobar_bogus');
        $this->assertEquals($preVal, $this->config->getCookieLifetime());
    }

    public function testSettingInvalidCookieLifetime2()
    {
        $returnMap =
            [
                [-1, false],
                ['Lax', true]
            ];
        $validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturnMap($returnMap);
        $this->getModel($validatorMock);
        $preVal = $this->config->getCookieLifetime();
        $this->config->setCookieLifetime(-1);
        $this->assertEquals($preVal, $this->config->getCookieLifetime());
    }

    public function testWrongMethodCall()
    {
        $this->getModel($this->validatorMock);
        $this->expectException('\BadMethodCallException');
        $this->expectExceptionMessage('Method "methodThatNotExist" does not exist in Magento\Framework\Session\Config');
        $this->config->methodThatNotExist();
    }

    public function testCookieSecureDefaultsToIniSettings()
    {
        $this->getModel($this->validatorMock);
        $this->assertSame((bool)ini_get('session.cookie_secure'), $this->config->getCookieSecure());
    }

    public function testCookieSecureIsMutable()
    {
        $this->getModel($this->validatorMock);
        $value = ini_get('session.cookie_secure') ? false : true;
        $this->config->setCookieSecure($value);
        $this->assertEquals($value, $this->config->getCookieSecure());
    }

    public function testCookieDomainIsMutable()
    {
        $this->getModel($this->validatorMock);
        $this->config->setCookieDomain('example.com');
        $this->assertEquals('example.com', $this->config->getCookieDomain());
    }

    public function testCookieDomainCanBeEmpty()
    {
        $this->getModel($this->validatorMock);
        $this->config->setCookieDomain('');
        $this->assertEquals('', $this->config->getCookieDomain());
    }

    public function testSettingInvalidCookieDomain()
    {
        $returnMap =
            [
                [24, false],
                ['Lax', true]
            ];
        $validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturnMap($returnMap);
        $this->getModel($validatorMock);
        $preVal = $this->config->getCookieDomain();
        $this->config->setCookieDomain(24);
        $this->assertEquals($preVal, $this->config->getCookieDomain());
    }

    public function testSettingInvalidCookieDomain2()
    {
        $returnMap =
            [
                ['D:\\WINDOWS\\System32\\drivers\\etc\\hosts', false],
                ['Lax', true]
            ];
        $validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturnMap($returnMap);
        $this->getModel($validatorMock);
        $preVal = $this->config->getCookieDomain();
        $this->config->setCookieDomain('D:\\WINDOWS\\System32\\drivers\\etc\\hosts');
        $this->assertEquals($preVal, $this->config->getCookieDomain());
    }

    public function testCookieHttpOnlyDefaultsToIniSettings()
    {
        $this->getModel($this->validatorMock);
        $this->assertSame((bool)ini_get('session.cookie_httponly'), $this->config->getCookieHttpOnly());
    }

    public function testCookieHttpOnlyIsMutable()
    {
        $this->getModel($this->validatorMock);
        $value = ini_get('session.cookie_httponly') ? false : true;
        $this->config->setCookieHttpOnly($value);
        $this->assertEquals($value, $this->config->getCookieHttpOnly());
    }

    public function testUseCookiesDefaultsToIniSettings()
    {
        $this->getModel($this->validatorMock);
        $this->assertSame((bool)ini_get('session.use_cookies'), $this->config->getUseCookies());
    }

    public function testUseCookiesIsMutable()
    {
        $this->getModel($this->validatorMock);
        $value = ini_get('session.use_cookies') ? false : true;
        $this->config->setUseCookies($value);
        $this->assertEquals($value, (bool)$this->config->getUseCookies());
    }

    public function testUseOnlyCookiesDefaultsToIniSettings()
    {
        $this->getModel($this->validatorMock);
        $this->assertSame((bool)ini_get('session.use_only_cookies'), $this->config->getUseOnlyCookies());
    }

    public function testUseOnlyCookiesIsMutable()
    {
        $this->getModel($this->validatorMock);
        $value = ini_get('session.use_only_cookies') ? false : true;
        $this->config->setOption('use_only_cookies', $value);
        $this->assertEquals($value, (bool)$this->config->getOption('use_only_cookies'));
    }

    public function testRefererCheckDefaultsToIniSettings()
    {
        $this->getModel($this->validatorMock);
        $this->assertSame(ini_get('session.referer_check'), $this->config->getRefererCheck());
    }

    public function testRefererCheckIsMutable()
    {
        $this->getModel($this->validatorMock);
        $this->config->setOption('referer_check', 'FOOBAR');
        $this->assertEquals('FOOBAR', $this->config->getOption('referer_check'));
    }

    public function testRefererCheckMayBeEmpty()
    {
        $this->getModel($this->validatorMock);
        $this->config->setOption('referer_check', '');
        $this->assertEquals('', $this->config->getOption('referer_check'));
    }

    public function testSetSavePath()
    {
        $this->getModel($this->validatorMock);
        $this->config->setSavePath('some_save_path');
        $this->assertEquals($this->config->getOption('save_path'), 'some_save_path');
    }

    /**
     * @param bool $isValidSame
     * @param bool $isValid
     * @param array $expected
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($isValidSame, $isValid, $expected)
    {
        $validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        if ($isValidSame) {
            $returnMap =
                [
                    [7200, $isValid],
                    ['/', $isValid],
                    ['init.host', $isValid],
                    ['Lax', true]
                ];
            $validatorMock->expects($this->any())
                ->method('isValid')
                ->willReturnMap($returnMap);
        } else {
            $returnMap =
                [
                    [3600, true],
                    [7200, false],
                    ['/', true],
                    ['init.host', true],
                    ['Lax', true]
                ];
            $validatorMock->expects($this->any())
                ->method('isValid')
                ->willReturnMap($returnMap);
        }

        $this->getModel($validatorMock);

        $this->assertEquals($expected, $this->config->getOptions());
    }

    /**
     * @return array
     */
    public static function constructorDataProvider()
    {
        return [
            'all valid' => [
                true,
                true,
                [
                    'session.cache_limiter' => 'private_no_expire',
                    'session.cookie_lifetime' => 7200,
                    'session.cookie_path' => '/',
                    'session.cookie_domain' => 'init.host',
                    'session.cookie_httponly' => false,
                    'session.cookie_secure' => false,
                    'session.save_handler' => 'files',
                    'session.cookie_samesite' => 'Lax'
                ],
            ],
            'all invalid' => [
                true,
                false,
                [
                    'session.cache_limiter' => 'private_no_expire',
                    'session.cookie_httponly' => false,
                    'session.cookie_secure' => false,
                    'session.save_handler' => 'files',
                    'session.cookie_samesite' => 'Lax'
                ],
            ],
            'invalid_valid' => [
                false,
                true,
                [
                    'session.cache_limiter' => 'private_no_expire',
                    'session.cookie_lifetime' => 3600,
                    'session.cookie_path' => '/',
                    'session.cookie_domain' => 'init.host',
                    'session.cookie_httponly' => false,
                    'session.cookie_secure' => false,
                    'session.save_handler' => 'files',
                    'session.cookie_samesite' => 'Lax'
                ],
            ],
        ];
    }

    /**
     * Get test model
     *
     * @param $validator
     * @return Config
     */
    protected function getModel($validator)
    {
        $this->requestMock = $this->createPartialMock(
            Http::class,
            ['getBasePath', 'isSecure', 'getHttpHost']
        );
        $this->requestMock->expects($this->atLeastOnce())->method('getBasePath')->willReturn('/');
        $this->requestMock->expects(
            $this->atLeastOnce()
        )->method(
            'getHttpHost'
        )->willReturn(
            'init.host'
        );

        $this->validatorFactoryMock = $this->getMockBuilder(ValidatorFactory::class)
            ->onlyMethods(['create'])
            ->addMethods(['setInstanceName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorFactoryMock->expects($this->any())
            ->method('setInstanceName')
            ->willReturnSelf();
        $this->validatorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($validator);

        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $getValueReturnMap = [
            ['test_web/test_cookie/test_cookie_lifetime', 'store', null, 7200],
            ['web/cookie/cookie_path', 'store', null, ''],
        ];
        $this->configMock->method('getValue')
            ->willReturnMap($getValueReturnMap);

        $filesystemMock = $this->createMock(Filesystem::class);
        $dirMock = $this->getMockForAbstractClass(WriteInterface::class);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($dirMock);

        $deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $deploymentConfigMock
            ->method('get')
            ->willReturnCallback(function ($configPath) {
                switch ($configPath) {
                    case Config::PARAM_SESSION_SAVE_METHOD:
                        return 'files';
                    case Config::PARAM_SESSION_CACHE_LIMITER:
                        return 'private_no_expire';
                    default:
                        return null;
                }
            });

        $this->config = $this->helper->getObject(
            Config::class,
            [
                'scopeConfig' => $this->configMock,
                'validatorFactory' => $this->validatorFactoryMock,
                'scopeType' => ScopeInterface::SCOPE_STORE,
                'cacheLimiter' => 'files',
                'lifetimePath' => 'test_web/test_cookie/test_cookie_lifetime',
                'request' => $this->requestMock,
                'filesystem' => $filesystemMock,
                'deploymentConfig' => $deploymentConfigMock,
            ]
        );
        return $this->config;
    }
}
