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

/**
 * Test class for \Magento\Framework\Session\Config
 */
namespace Magento\Framework\Session;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Session\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_configMock;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $_stringHelperMock;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestMock;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $_filesystem;

    protected function setUp()
    {
        $this->_configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->_stringHelperMock = $this->getMock('\Magento\Framework\Stdlib\String', array(), array(), '', false, false);
        $this->_requestMock = $this->getMock(
            '\Magento\Framework\App\Request\Http',
            array('getBasePath', 'isSecure', 'getHttpHost'),
            array(),
            '',
            false,
            false
        );
        $this->_requestMock->expects($this->atLeastOnce())->method('getBasePath')->will($this->returnValue('/'));
        $this->_requestMock->expects(
            $this->atLeastOnce()
        )->method(
            'getHttpHost'
        )->will(
            $this->returnValue('init.host')
        );
        $this->_appState = $this->getMock('\Magento\Framework\App\State', array('isInstalled'), array(), '', false, false);
        $this->_appState->expects($this->atLeastOnce())->method('isInstalled')->will($this->returnValue(true));
        $this->_filesystem = $this->getMock('\Magento\Framework\App\Filesystem', array(), array(), '', false, false);

        $this->config = new \Magento\Framework\Session\Config(
            $this->_configMock,
            $this->_stringHelperMock,
            $this->_requestMock,
            $this->_appState,
            $this->_filesystem,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            \Magento\Framework\Session\SaveHandlerInterface::DEFAULT_HANDLER,
            __DIR__
        );
    }

    public function testSetOptionsWrongType()
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Parameter provided to Magento\Framework\Session\Config::setOptions must be an array or Traversable'
        );
        $this->config->setOptions('');
    }

    /**
     * @dataProvider optionsProvider
     */
    public function testSetOptionsTranslatesUnderscoreSeparatedKeys($option, $getter, $value)
    {
        $options = array($option => $value);
        $this->config->setOptions($options);
        $this->assertSame($value, $this->config->{$getter}());
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

    public function testGetOptions()
    {
        $appStateProperty = new \ReflectionProperty('Magento\Framework\Session\Config', 'options');
        $appStateProperty->setAccessible(true);
        $original = $appStateProperty->getValue($this->config);
        $valueForTest = array('test' => 'test2');
        $appStateProperty->setValue($this->config, $valueForTest);
        $this->assertEquals($valueForTest, $this->config->getOptions());
        $this->assertEquals($valueForTest, $this->config->toArray());
        $appStateProperty->setValue($this->config, $original);
        $this->assertEquals($original, $this->config->getOptions());
        $this->assertEquals($original, $this->config->toArray());
    }

    public function testNameIsMutable()
    {
        $this->config->setName('FOOBAR');
        $this->assertEquals('FOOBAR', $this->config->getName());
    }

    public function testSaveHandlerDefaultsToIniSettings()
    {
        $this->assertSame(
            ini_get('session.save_handler'),
            $this->config->getSaveHandler(),
            var_export($this->config->toArray(), 1)
        );
    }

    public function testSaveHandlerIsMutable()
    {
        $this->config->setSaveHandler('user');
        $this->assertEquals('user', $this->config->getSaveHandler());
    }

    public function testCookieLifetimeIsMutable()
    {
        $this->config->setCookieLifetime(20);
        $this->assertEquals(20, $this->config->getCookieLifetime());
    }

    public function testCookieLifetimeCanBeZero()
    {
        $this->config->setCookieLifetime(0);
        $this->assertEquals(0, ini_get('session.cookie_lifetime'));
    }

    public function testSettingInvalidCookieLifetimeRaisesException()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Invalid cookie_lifetime; must be numeric');
        $this->config->setCookieLifetime('foobar_bogus');
    }

    public function testSettingInvalidCookieLifetimeRaisesException2()
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Invalid cookie_lifetime; must be a positive integer or zero'
        );
        $this->config->setCookieLifetime(-1);
    }

    public function testWrongMethodCall()
    {
        $this->setExpectedException(
            '\BadMethodCallException',
            'Method "methodThatNotExist" does not exist in Magento\Framework\Session\Config'
        );
        $this->config->methodThatNotExist();
    }

    public function testCookieSecureDefaultsToIniSettings()
    {
        $this->assertSame((bool)ini_get('session.cookie_secure'), $this->config->getCookieSecure());
    }

    public function testCookieSecureIsMutable()
    {
        $value = ini_get('session.cookie_secure') ? false : true;
        $this->config->setCookieSecure($value);
        $this->assertEquals($value, $this->config->getCookieSecure());
    }

    public function testCookieDomainIsMutable()
    {
        $this->config->setCookieDomain('example.com');
        $this->assertEquals('example.com', $this->config->getCookieDomain());
    }

    public function testCookieDomainCanBeEmpty()
    {
        $this->config->setCookieDomain('');
        $this->assertEquals('', $this->config->getCookieDomain());
    }

    public function testSettingInvalidCookieDomainRaisesException()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Invalid cookie domain: must be a string');
        $this->config->setCookieDomain(24);
    }

    public function testSettingInvalidCookieDomainRaisesException2()
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'does not match the expected structure for a DNS hostname'
        );
        $this->config->setCookieDomain('D:\\WINDOWS\\System32\\drivers\\etc\\hosts');
    }

    public function testCookieHttpOnlyDefaultsToIniSettings()
    {
        $this->assertSame((bool)ini_get('session.cookie_httponly'), $this->config->getCookieHttpOnly());
    }

    public function testCookieHttpOnlyIsMutable()
    {
        $value = ini_get('session.cookie_httponly') ? false : true;
        $this->config->setCookieHttpOnly($value);
        $this->assertEquals($value, $this->config->getCookieHttpOnly());
    }

    public function testUseCookiesDefaultsToIniSettings()
    {
        $this->assertSame((bool)ini_get('session.use_cookies'), $this->config->getUseCookies());
    }

    public function testUseCookiesIsMutable()
    {
        $value = ini_get('session.use_cookies') ? false : true;
        $this->config->setUseCookies($value);
        $this->assertEquals($value, (bool)$this->config->getUseCookies());
    }

    public function testUseOnlyCookiesDefaultsToIniSettings()
    {
        $this->assertSame((bool)ini_get('session.use_only_cookies'), $this->config->getUseOnlyCookies());
    }

    public function testUseOnlyCookiesIsMutable()
    {
        $value = ini_get('session.use_only_cookies') ? false : true;
        $this->config->setOption('use_only_cookies', $value);
        $this->assertEquals($value, (bool)$this->config->getOption('use_only_cookies'));
    }

    public function testRefererCheckDefaultsToIniSettings()
    {
        $this->assertSame(ini_get('session.referer_check'), $this->config->getRefererCheck());
    }

    public function testRefererCheckIsMutable()
    {
        $this->config->setOption('referer_check', 'FOOBAR');
        $this->assertEquals('FOOBAR', $this->config->getOption('referer_check'));
    }

    public function testRefererCheckMayBeEmpty()
    {
        $this->config->setOption('referer_check', '');
        $this->assertEquals('', $this->config->getOption('referer_check'));
    }

    public function testSetSavePath()
    {
        $this->config->setSavePath('some_save_path');
        $this->assertEquals($this->config->getOption('save_path'), 'some_save_path');
    }

    public function testSetLifetimePath()
    {
        $getValueReturnMap = [
            [
                'test_web/test_cookie/test_cookie_lifetime', 'store', null, 7200
            ],
            [
                'web/cookie/cookie_path', 'store', null, ''
            ],
        ];

        $this->_configMock
            ->method('getValue')
            ->will($this->returnValueMap($getValueReturnMap));

        $config = new \Magento\Framework\Session\Config(
            $this->_configMock,
            $this->_stringHelperMock,
            $this->_requestMock,
            $this->_appState,
            $this->_filesystem,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            \Magento\Framework\Session\SaveHandlerInterface::DEFAULT_HANDLER,
            __DIR__,
            null,
            'test_web/test_cookie/test_cookie_lifetime'
        );

        $this->assertEquals(7200, $config->getCookieLifetime());
    }
}
