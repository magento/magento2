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
namespace Magento\Captcha\Model;

class DefaultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Captcha default config data
     * @var array
     */
    protected static $_defaultConfig = array(
        'type' => 'default',
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
        'shown_to_logged_in_user' => array('contact_us' => 1),
        'always_for' => array(
            'user_create',
            'user_forgotpassword',
            'guest_checkout',
            'register_during_checkout',
            'contact_us'
        )
    );

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirMock;

    /**
     * path to fonts
     * @var array
     */
    protected $_fontPath = array(
        'LinLibertine' => array(
            'label' => 'LinLibertine',
            'path' => 'lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf'
        )
    );

    /**
     * @var \Magento\Captcha\Model\DefaultModel
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resLogFactory;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->session = $this->_getSessionStub();

        $this->_storeManager = $this->getMock(
            'Magento\Store\Model\StoreManager',
            array('getStore'),
            array(),
            '',
            false
        );
        $this->_storeManager->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_getStoreStub())
        );

        // \Magento\Customer\Model\Session
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManager');
        $this->_objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValueMap(
                array(
                    'Magento\Captcha\Helper\Data' => $this->_getHelperStub(),
                    'Magento\Customer\Model\Session' => $this->session
                )
            )
        );


        $this->_resLogFactory = $this->getMock(
            'Magento\Captcha\Model\Resource\LogFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_resLogFactory->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_getResourceModelStub())
        );

        $this->_object = new \Magento\Captcha\Model\DefaultModel(
            $this->session,
            $this->_getHelperStub(),
            $this->_resLogFactory,
            'user_create'
        );
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::getBlockName
     */
    public function testGetBlockName()
    {
        $this->assertEquals($this->_object->getBlockName(), 'Magento\Captcha\Block\Captcha\DefaultCaptcha');
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::isRequired
     */
    public function testIsRequired()
    {
        $this->assertTrue($this->_object->isRequired());
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::isCaseSensitive
     */
    public function testIsCaseSensitive()
    {
        self::$_defaultConfig['case_sensitive'] = '1';
        $this->assertEquals($this->_object->isCaseSensitive(), '1');
        self::$_defaultConfig['case_sensitive'] = '0';
        $this->assertEquals($this->_object->isCaseSensitive(), '0');
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::getFont
     */
    public function testGetFont()
    {
        $this->assertEquals($this->_object->getFont(), $this->_fontPath['LinLibertine']['path']);
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::getTimeout
     * @covers \Magento\Captcha\Model\DefaultModel::getExpiration
     */
    public function testGetTimeout()
    {
        $this->assertEquals($this->_object->getTimeout(), self::$_defaultConfig['timeout'] * 60);
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::isCorrect
     */
    public function testIsCorrect()
    {
        self::$_defaultConfig['case_sensitive'] = '1';
        $this->assertFalse($this->_object->isCorrect('abcdef5'));
        $sessionData = array('user_create_word' => array('data' => 'AbCdEf5', 'expires' => time() + 600));
        $this->_object->getSession()->setData($sessionData);
        self::$_defaultConfig['case_sensitive'] = '0';
        $this->assertTrue($this->_object->isCorrect('abcdef5'));
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::getImgSrc
     */
    public function testGetImgSrc()
    {
        $this->assertEquals(
            $this->_object->getImgSrc(),
            'http://localhost/pub/media/captcha/base/' . $this->_object->getId() . '.png'
        );
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::logAttempt
     */
    public function testLogAttempt()
    {
        $captcha = new \Magento\Captcha\Model\DefaultModel(
            $this->session,
            $this->_getHelperStub(),
            $this->_resLogFactory,
            'user_create'
        );

        $captcha->logAttempt('admin');

        $this->assertEquals($captcha->getSession()->getData('user_create_show_captcha'), 1);
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::getWord
     */
    public function testGetWord()
    {
        $this->assertEquals($this->_object->getWord(), 'AbCdEf5');
        $this->_object->getSession()->setData(
            array('user_create_word' => array('data' => 'AbCdEf5', 'expires' => time() - 360))
        );
        $this->assertNull($this->_object->getWord());
    }

    /**
     * Create stub session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSessionStub()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $sessionArgs = $helper->getConstructArguments(
            'Magento\Customer\Model\Session',
            array('storage' => new \Magento\Framework\Session\Storage())
        );
        $session = $this->getMock(
            'Magento\Customer\Model\Session',
            array('isLoggedIn', 'getUserCreateWord'),
            $sessionArgs
        );
        $session->expects($this->any())->method('isLoggedIn')->will($this->returnValue(false));

        $session->setData(array('user_create_word' => array('data' => 'AbCdEf5', 'expires' => time() + 600)));
        return $session;
    }

    /**
     * Create helper stub
     * @return \Magento\Captcha\Helper\Data
     */
    protected function _getHelperStub()
    {
        $helper = $this->getMockBuilder(
            'Magento\Captcha\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            array('getConfig', 'getFonts', '_getWebsiteCode', 'getImgUrl')
        )->getMock();

        $helper->expects(
            $this->any()
        )->method(
            'getConfig'
        )->will(
            $this->returnCallback('Magento\Captcha\Model\DefaultTest::getConfigNodeStub')
        );

        $helper->expects($this->any())->method('getFonts')->will($this->returnValue($this->_fontPath));

        $helper->expects($this->any())->method('_getWebsiteCode')->will($this->returnValue('base'));

        $helper->expects(
            $this->any()
        )->method(
            'getImgUrl'
        )->will(
            $this->returnValue('http://localhost/pub/media/captcha/base/')
        );


        return $helper;
    }

    /**
     * Get stub for resource model
     * @return \Magento\Captcha\Model\Resource\Log
     */
    protected function _getResourceModelStub()
    {
        $resourceModel = $this->getMock(
            'Magento\Captcha\Model\Resource\Log',
            array('countAttemptsByRemoteAddress', 'countAttemptsByUserLogin', 'logAttempt', '__wakeup'),
            array(),
            '',
            false
        );

        $resourceModel->expects($this->any())->method('logAttempt');

        $resourceModel->expects($this->any())->method('countAttemptsByRemoteAddress')->will($this->returnValue(0));

        $resourceModel->expects($this->any())->method('countAttemptsByUserLogin')->will($this->returnValue(3));
        return $resourceModel;
    }

    /**
     * Mock get config method
     * @static
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function getConfigNodeStub()
    {
        $args = func_get_args();
        $hashName = $args[0];

        if (array_key_exists($hashName, self::$_defaultConfig)) {
            return self::$_defaultConfig[$hashName];
        }

        throw new \InvalidArgumentException('Unknow id = ' . $hashName);
    }

    /**
     * Create store stub
     *
     * @return \Magento\Store\Model\Store
     */
    protected function _getStoreStub()
    {
        $store = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $store->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://localhost/pub/media/'));
        $store->expects($this->any())->method('isAdmin')->will($this->returnValue(false));
        return $store;
    }

    /**
     * @param boolean $expectedResult
     * @param string $formId
     * @dataProvider isShownToLoggedInUserDataProvider
     */
    public function testIsShownToLoggedInUser($expectedResult, $formId)
    {
        $captcha = new \Magento\Captcha\Model\DefaultModel(
            $this->session,
            $this->_getHelperStub(),
            $this->_resLogFactory,
            $formId
        );
        $this->assertEquals($expectedResult, $captcha->isShownToLoggedInUser());
    }

    public function isShownToLoggedInUserDataProvider()
    {
        return array(
            array(true, 'contact_us'),
            array(false, 'user_create'),
            array(false, 'user_forgotpassword'),
            array(false, 'guest_checkout')
        );
    }
}
