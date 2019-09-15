<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Model;

use Magento\Framework\Math\Random;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefaultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Expiration frame
     */
    const EXPIRE_FRAME = 86400;

    /**
     * Captcha default config data
     * @var array
     */
    protected static $_defaultConfig = [
        'type' => 'default',
        'enable' => '1',
        'font' => 'linlibertine',
        'mode' => 'after_fail',
        'forms' => 'user_forgotpassword,user_create',
        'failed_attempts_login' => '3',
        'failed_attempts_ip' => '1000',
        'timeout' => '7',
        'length' => '4-5',
        'symbols' => 'ABCDEFGHJKMnpqrstuvwxyz23456789',
        'case_sensitive' => '0',
        'shown_to_logged_in_user' => ['contact_us' => 1],
        'always_for' => [
            'user_create',
            'user_forgotpassword',
            'contact_us',
        ],
    ];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirMock;

    /**
     * path to fonts
     * @var array
     */
    protected $_fontPath = [
        'LinLibertine' => [
            'label' => 'LinLibertine',
            'path' => 'lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf',
        ],
    ];

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
    protected $session;

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

        $this->_storeManager = $this->createPartialMock(\Magento\Store\Model\StoreManager::class, ['getStore']);
        $this->_storeManager->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_getStoreStub())
        );

        // \Magento\Customer\Model\Session
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValueMap(
                [
                    \Magento\Captcha\Helper\Data::class => $this->_getHelperStub(),
                    \Magento\Customer\Model\Session::class => $this->session,
                ]
            )
        );

        $this->_resLogFactory = $this->createPartialMock(
            \Magento\Captcha\Model\ResourceModel\LogFactory::class,
            ['create']
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
        $this->assertEquals($this->_object->getBlockName(), \Magento\Captcha\Block\Captcha\DefaultCaptcha::class);
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
        $sessionData = [
            'user_create_word' => [
                'data' => 'AbCdEf5',
                'words' => 'AbCdEf5',
                'expires' => time() + self::EXPIRE_FRAME
            ]
        ];
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
            ['user_create_word' => ['data' => 'AbCdEf5', 'words' => 'AbCdEf5','expires' => time() - 360]]
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
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $sessionArgs = $helper->getConstructArguments(
            \Magento\Customer\Model\Session::class,
            ['storage' => new \Magento\Framework\Session\Storage()]
        );
        $session = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->setMethods(['isLoggedIn', 'getUserCreateWord'])
            ->setConstructorArgs($sessionArgs)
            ->getMock();
        $session->expects($this->any())->method('isLoggedIn')->will($this->returnValue(false));

        $session->setData(
            [
                'user_create_word' => [
                    'data' => 'AbCdEf5',
                    'words' => 'AbCdEf5',
                    'expires' => time() + self::EXPIRE_FRAME
                ]
            ]
        );
        return $session;
    }

    /**
     * Create helper stub
     * @return \Magento\Captcha\Helper\Data
     */
    protected function _getHelperStub()
    {
        $helper = $this->getMockBuilder(
            \Magento\Captcha\Helper\Data::class
        )->disableOriginalConstructor()->setMethods(
            ['getConfig', 'getFonts', '_getWebsiteCode', 'getImgUrl']
        )->getMock();

        $helper->expects(
            $this->any()
        )->method(
            'getConfig'
        )->will(
            $this->returnCallback('Magento\Captcha\Test\Unit\Model\DefaultTest::getConfigNodeStub')
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
     * @return \Magento\Captcha\Model\ResourceModel\Log
     */
    protected function _getResourceModelStub()
    {
        $resourceModel = $this->createPartialMock(
            \Magento\Captcha\Model\ResourceModel\Log::class,
            ['countAttemptsByRemoteAddress', 'countAttemptsByUserLogin', 'logAttempt', '__wakeup']
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
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['isAdmin', 'getBaseUrl']);
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

    /**
     * @return array
     */
    public function isShownToLoggedInUserDataProvider()
    {
        return [
            [true, 'contact_us'],
            [false, 'user_create'],
            [false, 'user_forgotpassword']
        ];
    }

    /**
     * @param string $string
     * @dataProvider generateWordProvider
     * @throws \ReflectionException
     */
    public function testGenerateWord($string)
    {
        $randomMock = $this->createMock(Random::class);
        $randomMock->expects($this->once())
            ->method('getRandomString')
            ->will($this->returnValue($string));
        $captcha = new \Magento\Captcha\Model\DefaultModel(
            $this->session,
            $this->_getHelperStub(),
            $this->_resLogFactory,
            'user_create',
            $randomMock
        );
        $method = new \ReflectionMethod($captcha, 'generateWord');
        $method->setAccessible(true);
        $this->assertEquals($string, $method->invoke($captcha));
    }
    /**
     * @return array
     */
    public function generateWordProvider()
    {
        return [
            ['ABC123'],
            ['1234567890'],
            ['The quick brown fox jumps over the lazy dog.']
        ];
    }
}
