<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Session;

/**
 * @magentoAppIsolation enabled
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Session\Config
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_cacheLimiter = 'private_no_expire';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $sessionManager \Magento\Framework\Session\SessionManager */
        $sessionManager = $this->_objectManager->create('Magento\Framework\Session\SessionManager');
        if ($sessionManager->isSessionExists()) {
            $sessionManager->writeClose();
        }
        $this->_model = $this->_objectManager->create(
            'Magento\Framework\Session\Config',
            array('saveMethod' => 'files', 'cacheLimiter' => $this->_cacheLimiter)
        );
    }

    protected function tearDown()
    {
        $this->_objectManager->removeSharedInstance('Magento\Framework\Session\Config');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testDefaultConfiguration()
    {
        $this->assertEquals(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\App\Filesystem'
            )->getPath(
                'session'
            ),
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

    public function testGetSessionSaveMethod()
    {
        $this->assertEquals('files', $this->_model->getSaveHandler());
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
        $options = array($option => $value);
        $this->_model->setOptions($options);
        $this->assertSame($value, $this->_model->{$getter}());
    }

    public function optionsProvider()
    {
        return array(
            array('save_path', 'getSavePath', __DIR__),
            array('name', 'getName', 'FOOBAR'),
            array('save_handler', 'getSaveHandler', 'user'),
            array('gc_probability', 'getGcProbability', 42),
            array('gc_divisor', 'getGcDivisor', 3),
            array('gc_maxlifetime', 'getGcMaxlifetime', 180),
            array('serialize_handler', 'getSerializeHandler', 'php_binary'),
            array('cookie_lifetime', 'getCookieLifetime', 180),
            array('cookie_path', 'getCookiePath', '/foo/bar'),
            array('cookie_domain', 'getCookieDomain', 'framework.zend.com'),
            array('cookie_secure', 'getCookieSecure', true),
            array('cookie_httponly', 'getCookieHttpOnly', true),
            array('use_cookies', 'getUseCookies', false),
            array('use_only_cookies', 'getUseOnlyCookies', true),
            array('referer_check', 'getRefererCheck', 'foobar'),
            array('entropy_file', 'getEntropyFile', __FILE__),
            array('entropy_length', 'getEntropyLength', 42),
            array('cache_limiter', 'getCacheLimiter', 'private'),
            array('cache_expire', 'getCacheExpire', 42),
            array('use_trans_sid', 'getUseTransSid', true),
            array('hash_function', 'getHashFunction', 'md5'),
            array('hash_bits_per_character', 'getHashBitsPerCharacter', 5),
            array('url_rewriter_tags', 'getUrlRewriterTags', 'a=href')
        );
    }

    public function testNameIsMutable()
    {
        $this->_model->setName('FOOBAR');
        $this->assertEquals('FOOBAR', $this->_model->getName());
    }

    public function testSaveHandlerIsMutable()
    {
        $this->_model->setSaveHandler('user');
        $this->assertEquals('user', $this->_model->getSaveHandler());
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

    public function testCookieSecureIsMutable()
    {
        $value = ini_get('session.cookie_secure') ? false : true;
        $this->_model->setCookieSecure($value);
        $this->assertEquals($value, $this->_model->getCookieSecure());
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

    public function testCookieHttpOnlyIsMutable()
    {
        $value = ini_get('session.cookie_httponly') ? false : true;
        $this->_model->setCookieHttpOnly($value);
        $this->assertEquals($value, $this->_model->getCookieHttpOnly());
    }

    public function testUseCookiesDefaultsToIniSettings()
    {
        $this->assertSame((bool)ini_get('session.use_cookies'), $this->_model->getUseCookies());
    }

    public function testUseCookiesIsMutable()
    {
        $value = ini_get('session.use_cookies') ? false : true;
        $this->_model->setUseCookies($value);
        $this->assertEquals($value, (bool)$this->_model->getUseCookies());
    }

    public function testUseOnlyCookiesDefaultsToIniSettings()
    {
        $this->assertSame((bool)ini_get('session.use_only_cookies'), $this->_model->getUseOnlyCookies());
    }

    public function testUseOnlyCookiesIsMutable()
    {
        $value = ini_get('session.use_only_cookies') ? false : true;
        $this->_model->setOption('use_only_cookies', $value);
        $this->assertEquals($value, (bool)$this->_model->getOption('use_only_cookies'));
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
}
