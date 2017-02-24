<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @magentoAppIsolation enabled
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Session\Config */
    protected $_model;

    /** @var string */
    protected $_cacheLimiter = 'private_no_expire';

    /** @var \Magento\TestFramework\ObjectManager */
    protected $_objectManager;

    /** @var string Default value for session.save_path setting */
    protected $defaultSavePath;

    /** @var \Magento\Framework\App\DeploymentConfig | \PHPUnit_Framework_MockObject_MockObject */
    protected $deploymentConfigMock;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $sessionManager \Magento\Framework\Session\SessionManager */
        $sessionManager = $this->_objectManager->create(\Magento\Framework\Session\SessionManager::class);
        if ($sessionManager->isSessionExists()) {
            $sessionManager->writeClose();
        }
        $this->deploymentConfigMock = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);

        $this->deploymentConfigMock->expects($this->at(0))
            ->method('get')
            ->with(Config::PARAM_SESSION_SAVE_PATH)
            ->will($this->returnValue(null));
        $this->deploymentConfigMock->expects($this->at(1))
            ->method('get')
            ->with(Config::PARAM_SESSION_CACHE_LIMITER)
            ->will($this->returnValue($this->_cacheLimiter));

        $this->_model = $this->_objectManager->create(
            \Magento\Framework\Session\Config::class,
            ['deploymentConfig' => $this->deploymentConfigMock]
        );
        $this->defaultSavePath = $this->_objectManager
            ->get(\Magento\Framework\Filesystem\DirectoryList::class)
            ->getPath(DirectoryList::SESSION);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testDefaultConfiguration()
    {
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        );
        $path = ini_get('session.save_path') ?:
            $filesystem->getDirectoryRead(DirectoryList::SESSION)->getAbsolutePath();

        $this->assertEquals(
            $path,
            $this->_model->getSavePath()
        );
        $this->assertEquals(
            \Magento\Framework\Session\Config::COOKIE_LIFETIME_DEFAULT,
            $this->_model->getCookieLifetime()
        );
        $this->assertEquals($this->_cacheLimiter, $this->_model->getCacheLimiter());
        $this->assertEquals('/', $this->_model->getCookiePath());
        $this->assertEquals('localhost', $this->_model->getCookieDomain());
        $this->assertEquals(false, $this->_model->getCookieSecure());
        $this->assertEquals(true, $this->_model->getCookieHttpOnly());
        $this->assertEquals($this->_model->getSavePath(), $this->_model->getOption('save_path'));
    }

    /**
     * Unable to add integration tests for testGetLifetimePathNonDefault
     *
     * Error: Cannot modify header information - headers already sent
     */
    public function testGetLifetimePathNonDefault()
    {
    }

    public function testSetOptionsInvalidValue()
    {
        $preValue = $this->_model->getOptions();
        $this->_model->setOptions('');
        $this->assertEquals($preValue, $this->_model->getOptions());
    }

    /**
     * @dataProvider optionsProvider
     */
    public function testSetOptions($option, $getter, $value)
    {
        $options = [$option => $value];
        $this->_model->setOptions($options);
        $this->assertSame($value, $this->_model->{$getter}());
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

    public function testNameIsMutable()
    {
        $this->_model->setName('FOOBAR');
        $this->assertEquals('FOOBAR', $this->_model->getName());
    }

    public function testCookieLifetimeIsMutable()
    {
        $this->_model->setCookieLifetime(20);
        $this->assertEquals(20, $this->_model->getCookieLifetime());
    }

    public function testCookieLifetimeCanBeZero()
    {
        $this->_model->setCookieLifetime(0);
        $this->assertEquals(0, $this->_model->getCookieLifetime());
    }

    public function testSettingInvalidCookieLifetime()
    {
        $preVal = $this->_model->getCookieLifetime();
        $this->_model->setCookieLifetime('foobar_bogus');
        $this->assertEquals($preVal, $this->_model->getCookieLifetime());
    }

    public function testSettingInvalidCookieLifetime2()
    {
        $preVal = $this->_model->getCookieLifetime();
        $this->_model->setCookieLifetime(-1);
        $this->assertEquals($preVal, $this->_model->getCookieLifetime());
    }

    public function testWrongMethodCall()
    {
        $this->setExpectedException(
            '\BadMethodCallException',
            'Method "methodThatNotExist" does not exist in Magento\Framework\Session\Config'
        );
        $this->_model->methodThatNotExist();
    }

    public function testCookieSecureDefaultsToIniSettings()
    {
        $this->assertSame((bool)ini_get('session.cookie_secure'), $this->_model->getCookieSecure());
    }

    public function testSetCookieSecureInOptions()
    {
        $this->_model->setCookieSecure(true);
        $this->assertTrue($this->_model->getCookieSecure());
    }

    public function testCookieDomainIsMutable()
    {
        $this->_model->setCookieDomain('example.com');
        $this->assertEquals('example.com', $this->_model->getCookieDomain());
    }

    public function testCookieDomainCanBeEmpty()
    {
        $this->_model->setCookieDomain('');
        $this->assertEquals('', $this->_model->getCookieDomain());
    }

    public function testSettingInvalidCookieDomain()
    {
        $preVal = $this->_model->getCookieDomain();
        $this->_model->setCookieDomain(24);
        $this->assertEquals($preVal, $this->_model->getCookieDomain());
    }

    public function testSettingInvalidCookieDomain2()
    {
        $preVal = $this->_model->getCookieDomain();
        $this->_model->setCookieDomain('D:\\WINDOWS\\System32\\drivers\\etc\\hosts');
        $this->assertEquals($preVal, $this->_model->getCookieDomain());
    }

    public function testSetCookieHttpOnlyInOptions()
    {
        $this->_model->setCookieHttpOnly(true);
        $this->assertTrue($this->_model->getCookieHttpOnly());
    }

    public function testUseCookiesDefaultsToIniSettings()
    {
        $this->assertSame((bool)ini_get('session.use_cookies'), $this->_model->getUseCookies());
    }

    public function testSetUseCookiesInOptions()
    {
        $this->_model->setUseCookies(true);
        $this->assertTrue($this->_model->getUseCookies());
    }

    public function testUseOnlyCookiesDefaultsToIniSettings()
    {
        $this->assertSame((bool)ini_get('session.use_only_cookies'), $this->_model->getUseOnlyCookies());
    }

    public function testSetUseOnlyCookiesInOptions()
    {
        $this->_model->setOption('use_only_cookies', true);
        $this->assertTrue((bool)$this->_model->getOption('use_only_cookies'));
    }

    public function testRefererCheckDefaultsToIniSettings()
    {
        $this->assertSame(ini_get('session.referer_check'), $this->_model->getRefererCheck());
    }

    public function testRefererCheckIsMutable()
    {
        $this->_model->setOption('referer_check', 'FOOBAR');
        $this->assertEquals('FOOBAR', $this->_model->getOption('referer_check'));
    }

    public function testRefererCheckMayBeEmpty()
    {
        $this->_model->setOption('referer_check', '');
        $this->assertEquals('', $this->_model->getOption('referer_check'));
    }

    public function testSetSavePath()
    {
        $this->_model->setSavePath('some_save_path');
        $this->assertEquals($this->_model->getOption('save_path'), 'some_save_path');
    }

    /**
     * @dataProvider savePathDataProvider
     */
    public function testConstructorSavePath($existing, $given, $expected)
    {
        $sessionSavePath = ini_get('session.save_path');
        if ($expected === 'default') {
            $expected = $this->defaultSavePath . '/';
        }
        $setup = ini_set('session.save_path', $existing);
        if ($setup === false) {
            $this->markTestSkipped('Cannot set session.save_path with ini_set');
        }

        $this->deploymentConfigMock->expects($this->at(0))
            ->method('get')
            ->with(Config::PARAM_SESSION_SAVE_PATH)
            ->will($this->returnValue($given));

        $this->_model = $this->_objectManager->create(
            \Magento\Framework\Session\Config::class,
            ['deploymentConfig' => $this->deploymentConfigMock]
        );
        $this->assertEquals($expected, $this->_model->getOption('save_path'));

        if ($sessionSavePath != ini_get('session.save_path')) {
            ini_set('session.save_path', $sessionSavePath);
        }
    }

    public function savePathDataProvider()
    {
        // preset value (null = not set), input value (null = not set), expected value
        $savePathGiven = 'explicit_save_path';
        $presetPath = 'preset_save_path';
        return [
            [null, $savePathGiven, $savePathGiven],
            [null, null, 'default'],
            [$presetPath, $savePathGiven, $savePathGiven],
            [$presetPath, null, $presetPath],
        ];
    }
}
