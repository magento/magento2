<?php
/**
 * Framework for testing Block_Adminhtml code
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Number of fields is necessary because of the number of fields used by multiple layers
 * of parent classes.
 *
 */
namespace Magento\Framework\TestFramework\Unit\Block;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Adminhtml extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_designMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sidResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_translatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messagesMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_controllerMock;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $_context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManager
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Math\Random
     */
    protected $_mathMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     */
    protected function setUp()
    {
        // These mocks are accessed via context
        $this->_designMock          = $this->_makeMock(\Magento\Framework\View\DesignInterface::class);
        $this->_sessionMock         = $this->_makeMock(\Magento\Framework\Session\Generic::class);
        $this->_sidResolver         = $this->_makeMock(\Magento\Framework\Session\SidResolver::class);
        $this->_translatorMock      = $this->_makeMock(\Magento\Framework\TranslateInterface::class);
        $this->_layoutMock          = $this->_makeMock(\Magento\Framework\View\Layout::class);
        $this->_requestMock         = $this->_makeMock(\Magento\Framework\App\RequestInterface::class);
        $this->_messagesMock        = $this->_makeMock(\Magento\Framework\View\Element\Messages::class);
        $this->_urlMock             = $this->_makeMock(\Magento\Framework\UrlInterface::class);
        $this->_eventManagerMock    = $this->_makeMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->_controllerMock      = $this->_makeMock(\Magento\Framework\App\FrontController::class);
        $this->_loggerMock          = $this->_makeMock(\Psr\Log\LoggerInterface::class);
        $this->_filesystemMock      = $this->_makeMock(\Magento\Framework\Filesystem::class);
        $this->_cacheMock           = $this->_makeMock(\Magento\Framework\App\CacheInterface::class);
        $this->_scopeConfigMock     = $this->_makeMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_storeManagerMock    = $this->_makeMock(\Magento\Store\Model\StoreManager::class);
        $assetRepoMock              = $this->_makeMock(\Magento\Framework\View\Asset\Repository::class);
        $viewConfigMock             = $this->_makeMock(\Magento\Framework\View\ConfigInterface::class);
        $viewFileSystemMock         = $this->_makeMock(\Magento\Framework\View\FileSystem::class);
        $templatePoolMock           = $this->_makeMock(\Magento\Framework\View\TemplateEnginePool::class);
        $authorizationMock          = $this->_makeMock(\Magento\Framework\AuthorizationInterface::class);
        $cacheStateMock             = $this->_makeMock(\Magento\Framework\App\Cache\StateInterface::class);
        $escaperMock                = $this->_makeMock(\Magento\Framework\Escaper::class);
        $filterManagerMock          = $this->_makeMock(\Magento\Framework\Filter\FilterManager::class);
        $backendSessionMock         = $this->_makeMock(\Magento\Backend\Model\Session::class);
        $appState                   = $this->_makeMock(\Magento\Framework\App\State::class);
        $this->_mathMock            = $this->_makeMock(\Magento\Framework\Math\Random::class);
        $this->_formKey             = $this->_makeMock(\Magento\Framework\Data\Form\FormKey::class);

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
     * @param array $args
     * @return mixed
     */
    public function translateCallback($args)
    {
        return $args[0]->getText();
    }
}
