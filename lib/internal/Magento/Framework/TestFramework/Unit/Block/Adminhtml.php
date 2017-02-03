<?php
/**
 * Framework for testing Block_Adminhtml code
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Number of fields is necessary because of the number of fields used by multiple layers
 * of parent classes.
 *
 */
namespace Magento\Framework\TestFramework\Unit\Block;

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
    protected $_sidResolver;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_translatorMock;

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
    protected $_context;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_loggerMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_filesystemMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_cacheMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_scopeConfigMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManager */
    protected $_storeManagerMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Math\Random */
    protected $_mathMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Form\FormKey */
    protected $_formKey;

    protected function setUp()
    {
        // These mocks are accessed via context
        $this->_designMock          = $this->_makeMock('Magento\Framework\View\DesignInterface');
        $this->_sessionMock         = $this->_makeMock('Magento\Framework\Session\Generic');
        $this->_sidResolver         = $this->_makeMock('Magento\Framework\Session\SidResolver');
        $this->_translatorMock      = $this->_makeMock('Magento\Framework\TranslateInterface');
        $this->_layoutMock          = $this->_makeMock('Magento\Framework\View\Layout');
        $this->_requestMock         = $this->_makeMock('Magento\Framework\App\RequestInterface');
        $this->_messagesMock        = $this->_makeMock('Magento\Framework\View\Element\Messages');
        $this->_urlMock             = $this->_makeMock('Magento\Framework\UrlInterface');
        $this->_eventManagerMock    = $this->_makeMock('Magento\Framework\Event\ManagerInterface');
        $this->_controllerMock      = $this->_makeMock('Magento\Framework\App\FrontController');
        $this->_loggerMock          = $this->_makeMock('Psr\Log\LoggerInterface');
        $this->_filesystemMock      = $this->_makeMock('Magento\Framework\Filesystem');
        $this->_cacheMock           = $this->_makeMock('Magento\Framework\App\CacheInterface');
        $this->_scopeConfigMock     = $this->_makeMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_storeManagerMock    = $this->_makeMock('Magento\Store\Model\StoreManager');
        $assetRepoMock              = $this->_makeMock('Magento\Framework\View\Asset\Repository');
        $viewConfigMock             = $this->_makeMock('Magento\Framework\View\ConfigInterface');
        $viewFileSystemMock         = $this->_makeMock('Magento\Framework\View\FileSystem');
        $templatePoolMock           = $this->_makeMock('Magento\Framework\View\TemplateEnginePool');
        $authorizationMock          = $this->_makeMock('Magento\Framework\AuthorizationInterface');
        $cacheStateMock             = $this->_makeMock('Magento\Framework\App\Cache\StateInterface');
        $escaperMock                = $this->_makeMock('Magento\Framework\Escaper');
        $filterManagerMock          = $this->_makeMock('Magento\Framework\Filter\FilterManager');
        $backendSessionMock         = $this->_makeMock('Magento\Backend\Model\Session');
        $appState                   = $this->_makeMock('Magento\Framework\App\State');
        $this->_mathMock            = $this->_makeMock('Magento\Framework\Math\Random');
        $this->_formKey             = $this->_makeMock('Magento\Framework\Data\Form\FormKey');

        $appState->setAreaCode(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $this->_translatorMock->expects(
            $this->any()
        )->method(
            'translate'
        )->will(
            $this->returnCallback([$this, 'translateCallback'])
        );

        $this->_context = new \Magento\Backend\Block\Template\Context(
            $this->_requestMock,
            $this->_layoutMock,
            $this->_eventManagerMock,
            $this->_urlMock,
            $this->_translatorMock,
            $this->_cacheMock,
            $this->_designMock,
            $this->_sessionMock,
            $this->_sidResolver,
            $this->_scopeConfigMock,
            $this->_controllerMock,
            $assetRepoMock,
            $viewConfigMock,
            $cacheStateMock,
            $this->_loggerMock,
            $escaperMock,
            $filterManagerMock,
            $this->_filesystemMock,
            $viewFileSystemMock,
            $templatePoolMock,
            $appState,
            $this->_storeManagerMock,
            $authorizationMock,
            $backendSessionMock,
            $this->_mathMock,
            $this->_formKey
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
        return $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
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

        return $object->expects($expects)->method($stubName)->will($return);
    }

    /**
     * Return the English text passed into the translate method
     *
     * @param $args
     * @return mixed
     */
    public function translateCallback($args)
    {
        return $args[0]->getText();
    }
}
