<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreStart
namespace {
    $mockPHPFunctions = false;
}

namespace Magento\Framework\Session {

    use Magento\Framework\App\Filesystem\DirectoryList;

    /**
     * Mock ini_get global function
     *
     * @return string
     */
    function ini_get($varName)
    {
        global $mockPHPFunctions;
        if ($mockPHPFunctions == 1) {
            switch ($varName) {
                case 'session.save_path':
                    return 'preset_save_path';
                case 'session.save_handler':
                    return 'php';
                default:
                    return '';
            }
        } elseif ($mockPHPFunctions == 2) {
            return null;
        }
        return call_user_func_array('\ini_get', func_get_args());
    }

    // @codingStandardsIgnoreEnd

    /**
     * @magentoAppIsolation enabled
     */
    class ConfigTest extends \PHPUnit\Framework\TestCase
    {
        /** @var string */
        private $_cacheLimiter = 'private_no_expire';

        /** @var \Magento\TestFramework\ObjectManager */
        private $_objectManager;

        /** @var string Default value for session.save_path setting */
        private $defaultSavePath;

        /** @var \Magento\Framework\App\DeploymentConfig | \PHPUnit\Framework\MockObject\MockObject */
        private $deploymentConfigMock;

        protected function setUp(): void
        {
            $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

            $this->deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
            $this->deploymentConfigMock
                ->method('get')
                ->willReturnCallback(function ($configPath) {
                    switch ($configPath) {
                        case Config::PARAM_SESSION_SAVE_METHOD:
                            return 'files';
                        case Config::PARAM_SESSION_CACHE_LIMITER:
                            return $this->_cacheLimiter;
                        default:
                            return null;
                    }
                });

            $this->defaultSavePath = $this->_objectManager
                ->get(\Magento\Framework\Filesystem\DirectoryList::class)
                ->getPath(DirectoryList::SESSION);
        }

        /**
         * @magentoAppIsolation enabled
         */
        public function testDefaultConfiguration()
        {
            $model = $this->getModel();
            /** @var \Magento\Framework\Filesystem $filesystem */
            $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Framework\Filesystem::class
            );
            $path = ini_get('session.save_path') ?:
                $filesystem->getDirectoryRead(DirectoryList::SESSION)->getAbsolutePath();

            $this->assertEquals(
                $path,
                $model->getSavePath()
            );
            $this->assertEquals(
                \Magento\Framework\Session\Config::COOKIE_LIFETIME_DEFAULT,
                $model->getCookieLifetime()
            );
            $this->assertEquals($this->_cacheLimiter, $model->getCacheLimiter());
            $this->assertEquals('/', $model->getCookiePath());
            $this->assertEquals('localhost', $model->getCookieDomain());
            $this->assertFalse($model->getCookieSecure());
            $this->assertTrue($model->getCookieHttpOnly());
            $this->assertEquals($model->getSavePath(), $model->getOption('save_path'));
        }

        public function testSetOptionsInvalidValue()
        {
            $model = $this->getModel();
            $preValue = $model->getOptions();
            $model->setOptions('');
            $this->assertEquals($preValue, $model->getOptions());
        }

        /**
         * @dataProvider optionsProvider
         */
        public function testSetOptions($option, $getter, $value)
        {
            $model = $this->getModel();
            $options = [$option => $value];
            $model->setOptions($options);
            $this->assertSame($value, $model->{$getter}());
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
            $model = $this->getModel();
            $model->setName('FOOBAR');
            $this->assertEquals('FOOBAR', $model->getName());
        }

        public function testCookieLifetimeIsMutable()
        {
            $model = $this->getModel();
            $model->setCookieLifetime(20);
            $this->assertEquals(20, $model->getCookieLifetime());
        }

        public function testCookieLifetimeCanBeZero()
        {
            $model = $this->getModel();
            $model->setCookieLifetime(0);
            $this->assertEquals(0, $model->getCookieLifetime());
        }

        public function testSettingInvalidCookieLifetime()
        {
            $model = $this->getModel();
            $preVal = $model->getCookieLifetime();
            $model->setCookieLifetime('foobar_bogus');
            $this->assertEquals($preVal, $model->getCookieLifetime());
        }

        public function testSettingInvalidCookieLifetime2()
        {
            $model = $this->getModel();
            $preVal = $model->getCookieLifetime();
            $model->setCookieLifetime(-1);
            $this->assertEquals($preVal, $model->getCookieLifetime());
        }

        public function testWrongMethodCall()
        {
            $model = $this->getModel();
            $this->expectException(\BadMethodCallException::class);
            $this->expectExceptionMessage(
                'Method "methodThatNotExist" does not exist in Magento\Framework\Session\Config'
            );
            $model->methodThatNotExist();
        }

        public function testCookieSecureDefaultsToIniSettings()
        {
            $model = $this->getModel();
            $this->assertSame((bool)ini_get('session.cookie_secure'), $model->getCookieSecure());
        }

        public function testSetCookieSecureInOptions()
        {
            $model = $this->getModel();
            $model->setCookieSecure(true);
            $this->assertTrue($model->getCookieSecure());
        }

        public function testCookieDomainIsMutable()
        {
            $model = $this->getModel();
            $model->setCookieDomain('example.com');
            $this->assertEquals('example.com', $model->getCookieDomain());
        }

        public function testCookieDomainCanBeEmpty()
        {
            $model = $this->getModel();
            $model->setCookieDomain('');
            $this->assertEquals('', $model->getCookieDomain());
        }

        public function testSettingInvalidCookieDomain()
        {
            $model = $this->getModel();
            $preVal = $model->getCookieDomain();
            $model->setCookieDomain(24);
            $this->assertEquals($preVal, $model->getCookieDomain());
        }

        public function testSettingInvalidCookieDomain2()
        {
            $model = $this->getModel();
            $preVal = $model->getCookieDomain();
            $model->setCookieDomain('D:\\WINDOWS\\System32\\drivers\\etc\\hosts');
            $this->assertEquals($preVal, $model->getCookieDomain());
        }

        public function testSetCookieHttpOnlyInOptions()
        {
            $model = $this->getModel();
            $model->setCookieHttpOnly(true);
            $this->assertTrue($model->getCookieHttpOnly());
        }

        public function testUseCookiesDefaultsToIniSettings()
        {
            $model = $this->getModel();
            $this->assertSame((bool)ini_get('session.use_cookies'), $model->getUseCookies());
        }

        public function testSetUseCookiesInOptions()
        {
            $model = $this->getModel();
            $model->setUseCookies(true);
            $this->assertTrue($model->getUseCookies());
        }

        public function testUseOnlyCookiesDefaultsToIniSettings()
        {
            $model = $this->getModel();
            $this->assertSame((bool)ini_get('session.use_only_cookies'), $model->getUseOnlyCookies());
        }

        public function testSetUseOnlyCookiesInOptions()
        {
            $model = $this->getModel();
            $model->setOption('use_only_cookies', true);
            $this->assertTrue((bool)$model->getOption('use_only_cookies'));
        }

        public function testRefererCheckDefaultsToIniSettings()
        {
            $model = $this->getModel();
            $this->assertSame(ini_get('session.referer_check'), $model->getRefererCheck());
        }

        public function testRefererCheckIsMutable()
        {
            $model = $this->getModel();
            $model->setOption('referer_check', 'FOOBAR');
            $this->assertEquals('FOOBAR', $model->getOption('referer_check'));
        }

        public function testRefererCheckMayBeEmpty()
        {
            $model = $this->getModel();
            $model->setOption('referer_check', '');
            $this->assertEquals('', $model->getOption('referer_check'));
        }

        public function testSetSavePath()
        {
            $model = $this->getModel();
            $model->setSavePath('some_save_path');
            $this->assertEquals($model->getOption('save_path'), 'some_save_path');
        }

        /**
         * @param $mockPHPFunctionNum
         * @param $givenSavePath
         * @param $expectedSavePath
         * @param $givenSaveHandler
         * @param $expectedSaveHandler
         * @dataProvider constructorDataProvider
         */
        public function testConstructor(
            $mockPHPFunctionNum,
            $givenSavePath,
            $expectedSavePath,
            $givenSaveHandler,
            $expectedSaveHandler
        ) {
            global $mockPHPFunctions;
            $mockPHPFunctions = $mockPHPFunctionNum;

            $sessionSaveHandler = ini_get('session.save_handler');
            if ($expectedSavePath === 'default') {
                $expectedSavePath = $this->defaultSavePath . '/';
            }
            if ($expectedSaveHandler === 'php') {
                $expectedSaveHandler = $sessionSaveHandler;
            }

            $deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
            $deploymentConfigMock
                ->method('get')
                ->willReturnCallback(function ($configPath) use ($givenSavePath, $givenSaveHandler) {
                    switch ($configPath) {
                        case Config::PARAM_SESSION_SAVE_METHOD:
                            return $givenSaveHandler;
                        case Config::PARAM_SESSION_CACHE_LIMITER:
                            return $this->_cacheLimiter;
                        case Config::PARAM_SESSION_SAVE_PATH:
                            return $givenSavePath;
                        default:
                            return null;
                    }
                });

            $model = $this->_objectManager->create(
                \Magento\Framework\Session\Config::class,
                ['deploymentConfig' => $deploymentConfigMock]
            );
            $this->assertEquals($expectedSavePath, $model->getOption('save_path'));
            $this->assertEquals($expectedSaveHandler, $model->getOption('session.save_handler'));
            global $mockPHPFunctions;
            $mockPHPFunctions = false;
        }

        public function constructorDataProvider()
        {
            // preset value (null = not set), input value (null = not set), expected value
            $savePathGiven = 'explicit_save_path';
            $presetPath = 'preset_save_path';
            return [
                [2, $savePathGiven, $savePathGiven, 'db', 'db'],
                [2, null, 'default', 'redis', 'redis'],
                [1, $savePathGiven, $savePathGiven, null, 'php'],
                [1, null, $presetPath, 'files', 'files'],
            ];
        }

        private function getModel(): \Magento\Framework\Session\Config
        {
            return $this->_objectManager->create(
                \Magento\Framework\Session\Config::class,
                ['deploymentConfig' => $this->deploymentConfigMock]
            );
        }
    }
}
