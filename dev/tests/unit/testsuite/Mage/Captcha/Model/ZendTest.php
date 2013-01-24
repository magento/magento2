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
 * @category    Magento
 * @package     Mage_Captcha
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Captcha_Model_ZendTest extends PHPUnit_Framework_TestCase
{
    /**
     * Captcha default config data
     * @var array
     */
    protected static $_defaultConfig = array(
        'type' => 'zend',
        'enable' => '1',
        'font' => 'linlibertine',
        'mode' => 'after_fail',
        'forms' => 'user_forgotpassword,user_create,guest_checkout,register_during_checkout',
        'failed_attempts_login' => '3',
        'failed_attempts_ip' => '1000',
        'timeout' => '7',
        'length' => '4-5',
        'symbols' => 'ABCDEFGHJKMnpqrstuvwxyz23456789',
        'case_sensitive' => '0',
        'always_for' => array(
            'user_create',
            'user_forgotpassword',
            'guest_checkout',
            'register_during_checkout',
        ),
    );

    /**
     * path to fonts
     * @var array
     */
    protected $_fontPath = array(
        'LinLibertine' => array(
            'label' => 'LinLibertine',
            'path' => 'lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf'
        )
    );

    /**
     * @var Mage_Captcha_Model_Zend
     */
    protected $_object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->_object = new Mage_Captcha_Model_Zend(
            array(
                'formId' => 'user_create',
                'helper' => $this->_getHelperStub(),
                'session' => $this->_getSessionStub()
            )
        );
    }

    /**
     * @covers Mage_Captcha_Model_Zend::getBlockName
     */
    public function testGetBlockName()
    {
        $this->assertEquals($this->_object->getBlockName(), 'Mage_Captcha_Block_Captcha_Zend');
    }

    /**
     * @covers Mage_Captcha_Model_Zend::isRequired
     */
    public function testIsRequired()
    {
        $this->assertTrue($this->_object->isRequired());
    }

    /**
     * @covers Mage_Captcha_Model_Zend::isCaseSensitive
     */
    public function testIsCaseSensitive()
    {
        self::$_defaultConfig['case_sensitive'] = '1';
        $this->assertEquals($this->_object->isCaseSensitive(), '1');
        self::$_defaultConfig['case_sensitive'] = '0';
        $this->assertEquals($this->_object->isCaseSensitive(), '0');
    }

    /**
     * @covers Mage_Captcha_Model_Zend::getFont
     */
    public function testGetFont()
    {
        $this->assertEquals(
            $this->_object->getFont(),
            $this->_fontPath['LinLibertine']['path']
        );
    }

    /**
     * @covers Mage_Captcha_Model_Zend::getTimeout
     * @covers Mage_Captcha_Model_Zend::getExpiration
     */
    public function testGetTimeout()
    {
        $this->assertEquals(
            $this->_object->getTimeout(),
            self::$_defaultConfig['timeout'] * 60
        );
    }

    /**
     * @covers Mage_Captcha_Model_Zend::isCorrect
     */
    public function testIsCorrect()
    {
        self::$_defaultConfig['case_sensitive'] = '1';
        $this->assertFalse($this->_object->isCorrect('abcdef5'));
        $sessionData = array(
            'user_create_word' => array(
                'data' => 'AbCdEf5',
                'expires' => time() + 600
            )
        );
        $this->_object->getSession()->setData($sessionData);
        self::$_defaultConfig['case_sensitive'] = '0';
        $this->assertTrue($this->_object->isCorrect('abcdef5'));
    }

    /**
     * @covers Mage_Captcha_Model_Zend::getImgSrc
     */
    public function testGetImgSrc()
    {
        $this->assertEquals(
            $this->_object->getImgSrc(),
            'http://localhost/pub/media/captcha/base/' . $this->_object->getId() . '.png'
        );
    }

    /**
     * @covers Mage_Captcha_Model_Zend::logAttempt
     */
    public function testLogAttempt()
    {
        $resourceModel = $this->_getResourceModelStub();

        $captcha = new Mage_Captcha_Model_Zend(
            array(
                'formId' => 'user_create',
                'helper' => $this->_getHelperStub(),
                'session' => $this->_getSessionStub(),
                'resourceModel' => $resourceModel,
            )
        );
        $captcha->logAttempt('admin');
        $this->assertEquals($captcha->getSession()->getData('user_create_show_captcha'), 1);
    }

    /**
     * @covers Mage_Captcha_Model_Zend::getWord
     */
    public function testGetWord()
    {
        $this->assertEquals($this->_object->getWord(), 'AbCdEf5');
        $this->_object->getSession()->setData(
            array(
                'user_create_word' => array(
                    'data' => 'AbCdEf5',
                    'expires' => time() - 60
                )
            )
        );
        $this->assertNull($this->_object->getWord());
    }

    /**
     * Create stub session object
     * @return Mage_Customer_Model_Session
     */
    protected function _getSessionStub()
    {
        $session = $this->getMock(
            'Mage_Customer_Model_Session',
            array('isLoggedIn'),
            array(), '', false
        );

        $session->expects($this->any())
            ->method('Mage_Customer_Model_Session')
            ->will($this->returnValue(true));

        $session->setData(
            array(
                'user_create_word' => array(
                    'data' => 'AbCdEf5',
                    'expires' => time() + 600
                )
            )
        );

        return $session;
    }

    /**
     * Create helper stub
     * @return Mage_Captcha_Helper_Data
     */
    protected function _getHelperStub()
    {
        $helper = $this->getMockBuilder('Mage_Captcha_Helper_Data')
            ->disableOriginalConstructor()
            ->setMethods(array('getConfigNode', 'getFonts', '_getWebsiteCode', 'getImgUrl'))
            ->getMock();

        $helper->expects($this->any())
            ->method('getConfigNode')
            ->will($this->returnCallback('Mage_Captcha_Model_ZendTest::getConfigNodeStub'));

        $helper->expects($this->any())
            ->method('getFonts')
            ->will($this->returnValue($this->_fontPath));

        $helper->expects($this->any())
            ->method('_getWebsiteCode')
            ->will($this->returnValue('base'));

        $helper->expects($this->any())
            ->method('getImgUrl')
            ->will($this->returnValue('http://localhost/pub/media/captcha/base/'));


        return $helper;
    }

    /**
     * Get stub for resource model
     * @return Mage_Captcha_Model_Resource_Log
     */
    protected function _getResourceModelStub()
    {
        $resourceModel = $this->getMock(
            'Mage_Captcha_Model_Resource_Log',
            array('countAttemptsByRemoteAddress', 'countAttemptsByUserLogin', 'logAttempt'),
            array(), '', false
        );

        $resourceModel->expects($this->once())
            ->method('logAttempt');

        $resourceModel->expects($this->any())
            ->method('countAttemptsByRemoteAddress')
            ->will($this->returnValue(0));

        $resourceModel->expects($this->any())
            ->method('countAttemptsByUserLogin')
            ->will($this->returnValue(3));
        return $resourceModel;
    }

    /**
     * Mock get config method
     * @static
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getConfigNodeStub()
    {
        $args = func_get_args();
        $hashName = $args[0];

        if (array_key_exists($hashName, self::$_defaultConfig)) {
            return self::$_defaultConfig[$hashName];
        }

        throw new InvalidArgumentException('Unknow id = ' . $hashName);
    }
}
