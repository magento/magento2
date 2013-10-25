<?php
/**
 * Framework for testing Block_Adminhtml code
 *
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
 * @package     unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Number of fields is necessary because of the number of fields used by multiple layers
 * of parent classes.
 *
 */
namespace Magento\Test\Block;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Adminhtml extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_designMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_sessionMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected  $_translatorMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_layoutMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_requestMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_messagesMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_urlMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_eventManagerMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_controllerMock;

    /** @var  \Magento\Backend\Block\Template\Context */
    protected  $_context;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_dirMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_loggerMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_filesystemMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_cacheMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_storeConfigMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_helperFactoryMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\StoreManager */
    protected $_storeManagerMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\LocaleInterface */
    protected $_localeMock;

    protected function setUp()
    {
        // These mocks are accessed via context
        $this->_designMock         = $this->_makeMock('Magento\View\DesignInterface');
        $this->_sessionMock         = $this->_makeMock('Magento\Core\Model\Session');
        $this->_translatorMock      = $this->_makeMock('Magento\Core\Model\Translate');
        $this->_layoutMock          = $this->_makeMock('Magento\Core\Model\Layout');
        $this->_requestMock         = $this->_makeMock('Magento\App\RequestInterface');
        $this->_messagesMock        = $this->_makeMock('Magento\Core\Block\Messages');
        $this->_urlMock             = $this->_makeMock('Magento\UrlInterface');
        $this->_eventManagerMock    = $this->_makeMock('Magento\Event\ManagerInterface');
        $this->_controllerMock      = $this->_makeMock('Magento\App\FrontController');
        $this->_dirMock             = $this->_makeMock('Magento\App\Dir');
        $this->_loggerMock          = $this->_makeMock('Magento\Core\Model\Logger');
        $this->_filesystemMock      = $this->_makeMock('Magento\Filesystem');
        $this->_cacheMock           = $this->_makeMock('Magento\Core\Model\CacheInterface');
        $this->_storeConfigMock     = $this->_makeMock('Magento\Core\Model\Store\Config');
        $this->_storeManagerMock    = $this->_makeMock('Magento\Core\Model\StoreManager');
        $this->_helperFactoryMock   = $this->_makeMock('Magento\Core\Model\Factory\Helper');
        $viewUrlMock                = $this->_makeMock('Magento\Core\Model\View\Url');
        $viewConfigMock             = $this->_makeMock('Magento\View\ConfigInterface');
        $viewFileSystemMock         = $this->_makeMock('Magento\Core\Model\View\FileSystem');
        $templatePoolMock           = $this->_makeMock('Magento\Core\Model\TemplateEngine\Pool');
        $authorizationMock          = $this->_makeMock('Magento\AuthorizationInterface');
        $cacheStateMock             = $this->_makeMock('Magento\Core\Model\Cache\StateInterface');
        $appMock                    = $this->_makeMock('Magento\Core\Model\App');
        $backendSessionMock         = $this->_makeMock('Magento\Backend\Model\Session');
        $this->_localeMock          = $this->_makeMock('Magento\Core\Model\LocaleInterface');

        $this->_translatorMock
            ->expects($this->any())
            ->method('translate')
            ->will($this->returnCallback(array($this, 'translateCallback')));

        $this->_context = new \Magento\Backend\Block\Template\Context(
            $this->_storeManagerMock,
            $this->_requestMock,
            $this->_layoutMock,
            $this->_eventManagerMock,
            $this->_urlMock,
            $this->_translatorMock,
            $this->_cacheMock,
            $this->_designMock,
            $this->_sessionMock,
            $this->_storeConfigMock,
            $this->_controllerMock,
            $this->_helperFactoryMock,
            $viewUrlMock,
            $viewConfigMock,
            $cacheStateMock,
            $this->_dirMock,
            $this->_loggerMock,
            $this->_filesystemMock,
            $viewFileSystemMock,
            $templatePoolMock,
            $authorizationMock,
            $appMock,
            $backendSessionMock,
            $this->_localeMock
        );
    }

    /**
     * Generates a mocked object
     *
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _makeMock($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Sets up a stubbed method with specified behavior and expectations
     *
     * @param \PHPUnit_Framework_MockObject_MockObject                       $object
     * @param string                                                        $stubName
     * @param mixed                                                         $return
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount|null        $expects
     *
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function _setStub(
        \PHPUnit_Framework_MockObject_MockObject $object,
        $stubName,
        $return = null,
        $expects = null
    ) {
        $expects = isset($expects) ? $expects : $this->any();
        $return = isset($return) ? $this->returnValue($return) : $this->returnSelf();

        return $object->expects($expects)
            ->method($stubName)
            ->will($return);
    }

    /**
     * Return the English text passed into the __() translate method
     *
     * @param $args
     * @return mixed
     */
    public function translateCallback($args)
    {
        return $args[0]->getText();
    }
}
