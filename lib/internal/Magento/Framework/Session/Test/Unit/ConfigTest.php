<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\Session\Config
 */
namespace Magento\Framework\Session\Test\Unit;

use \Magento\Framework\Session\Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Session\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\ValidatorFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorFactoryMock;

    /**
     * @var \Magento\Framework\Validator\ValidatorInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Filesystem | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    protected function setUp()
    {
        $this->helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

    public function optionsProvider()
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
            ['url_rewriter_tags', 'getUrlRewriterTags', 'a=href']
        ];
    }

    public function testGetOptions()
    {
        $this->getModel($this->validatorMock);
        $appStateProperty = new \ReflectionProperty(\Magento\Framework\Session\Config::class, 'options');
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
        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(false);
        $this->getModel($validatorMock);
        $preVal = $this->config->getCookieLifetime();
        $this->config->setCookieLifetime('foobar_bogus');
        $this->assertEquals($preVal, $this->config->getCookieLifetime());
    }

    public function testSettingInvalidCookieLifetime2()
    {
        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(false);
        $this->getModel($validatorMock);
        $preVal = $this->config->getCookieLifetime();
        $this->config->setCookieLifetime(-1);
        $this->assertEquals($preVal, $this->config->getCookieLifetime());
    }

    public function testWrongMethodCall()
    {
        $this->getModel($this->validatorMock);
        $this->setExpectedException(
            '\BadMethodCallException',
            'Method "methodThatNotExist" does not exist in Magento\Framework\Session\Config'
        );
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
        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(false);
        $this->getModel($validatorMock);
        $preVal = $this->config->getCookieDomain();
        $this->config->setCookieDomain(24);
        $this->assertEquals($preVal, $this->config->getCookieDomain());
    }

    public function testSettingInvalidCookieDomain2()
    {
        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(false);
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
        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        if ($isValidSame) {
            $validatorMock->expects($this->any())
                ->method('isValid')
                ->willReturn($isValid);
        } else {
            for ($x = 0; $x<6; $x++) {
                if ($x % 2 == 0) {
                    $validatorMock->expects($this->at($x))
                        ->method('isValid')
                        ->willReturn(false);
                } else {
                    $validatorMock->expects($this->at($x))
                        ->method('isValid')
                        ->willReturn(true);
                }
            }
        }

        $this->getModel($validatorMock);

        $this->assertEquals($expected, $this->config->getOptions());
    }

    public function constructorDataProvider()
    {
        return [
            'all valid' => [
                true,
                true,
                [
                    'session.cache_limiter' => 'files',
                    'session.cookie_lifetime' => 7200,
                    'session.cookie_path' => '/',
                    'session.cookie_domain' => 'init.host',
                    'session.cookie_httponly' => false,
                    'session.cookie_secure' => false,
                ],
            ],
            'all invalid' => [
                true,
                false,
                [
                    'session.cache_limiter' => 'files',
                    'session.cookie_httponly' => false,
                    'session.cookie_secure' => false,
                ],
            ],
            'invalid_valid' => [
                false,
                true,
                [
                    'session.cache_limiter' => 'files',
                    'session.cookie_lifetime' => 3600,
                    'session.cookie_path' => '/',
                    'session.cookie_domain' => 'init.host',
                    'session.cookie_httponly' => false,
                    'session.cookie_secure' => false,
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
        $this->requestMock = $this->getMock(
            \Magento\Framework\App\Request\Http::class,
            ['getBasePath', 'isSecure', 'getHttpHost'],
            [],
            '',
            false,
            false
        );
        $this->requestMock->expects($this->atLeastOnce())->method('getBasePath')->will($this->returnValue('/'));
        $this->requestMock->expects(
            $this->atLeastOnce()
        )->method(
            'getHttpHost'
        )->will(
            $this->returnValue('init.host')
        );

        $this->validatorFactoryMock = $this->getMockBuilder(\Magento\Framework\ValidatorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorFactoryMock->expects($this->any())
            ->method('setInstanceName')
            ->willReturnSelf();
        $this->validatorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($validator);

        $this->configMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $getValueReturnMap = [
            ['test_web/test_cookie/test_cookie_lifetime', 'store', null, 7200],
            ['web/cookie/cookie_path', 'store', null, ''],
        ];
        $this->configMock->method('getValue')
            ->will($this->returnValueMap($getValueReturnMap));

        $filesystemMock = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $dirMock = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($dirMock));

        $deploymentConfigMock = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $deploymentConfigMock->expects($this->at(0))
            ->method('get')
            ->with(Config::PARAM_SESSION_SAVE_PATH)
            ->will($this->returnValue(null));
        $deploymentConfigMock->expects($this->at(1))
            ->method('get')
            ->with(Config::PARAM_SESSION_CACHE_LIMITER)
            ->will($this->returnValue('files'));

        $this->config = $this->helper->getObject(
            \Magento\Framework\Session\Config::class,
            [
                'scopeConfig' => $this->configMock,
                'validatorFactory' => $this->validatorFactoryMock,
                'scopeType' => \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
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
