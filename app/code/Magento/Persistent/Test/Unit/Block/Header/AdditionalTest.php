<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Test\Unit\Block\Header;

/**
 * Class AdditionalTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdditionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Helper\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerViewHelperMock;

    /**
     * @var \Magento\Persistent\Helper\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentSessionHelperMock;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheStateMock;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidResolverMock;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Persistent\Block\Header\Additional
     */
    protected $additional;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->contextMock = $this->getMock(
            'Magento\Framework\View\Element\Template\Context',
            [
                'getEventManager',
                'getScopeConfig',
                'getCacheState',
                'getCache',
                'getInlineTranslation',
                'getSidResolver',
                'getSession',
                'getEscaper',
                'getUrlBuilder'
            ],
            [],
            '',
            false
        );
        $this->customerViewHelperMock = $this->getMock(
            'Magento\Customer\Helper\View',
            [],
            [],
            '',
            false
        );
        $this->persistentSessionHelperMock = $this->getMock(
            'Magento\Persistent\Helper\Session',
            ['getSession'],
            [],
            '',
            false
        );
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\CustomerRepositoryInterface',
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );

        $this->eventManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Event\ManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->scopeConfigMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false,
            true,
            true,
            ['getValue']
        );
        $this->cacheStateMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\Cache\StateInterface',
            [],
            '',
            false,
            true,
            true,
            ['isEnabled']
        );
        $this->cacheMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\CacheInterface',
            [],
            '',
            false,
            true,
            true,
            ['load']
        );
        $this->sidResolverMock = $this->getMockForAbstractClass(
            'Magento\Framework\Session\SidResolverInterface',
            [],
            '',
            false,
            true,
            true,
            ['getSessionIdQueryParam']
        );
        $this->sessionMock = $this->getMockForAbstractClass(
            'Magento\Framework\Session\SessionManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['getSessionId']
        );
        $this->escaperMock = $this->getMockForAbstractClass(
            'Magento\Framework\Escaper',
            [],
            '',
            false,
            true,
            true,
            ['escapeHtml']
        );
        $this->urlBuilderMock = $this->getMockForAbstractClass(
            'Magento\Framework\UrlInterface',
            [],
            '',
            false,
            true,
            true,
            ['getUrl']
        );

        $this->contextMock->expects($this->once())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        $this->contextMock->expects($this->once())
            ->method('getCacheState')
            ->willReturn($this->cacheStateMock);
        $this->contextMock->expects($this->once())
            ->method('getCache')
            ->willReturn($this->cacheMock);
        $this->contextMock->expects($this->once())
            ->method('getSidResolver')
            ->willReturn($this->sidResolverMock);
        $this->contextMock->expects($this->once())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->contextMock->expects($this->once())
            ->method('getEscaper')
            ->willReturn($this->escaperMock);
        $this->contextMock->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->additional = $this->objectManager->getObject(
            'Magento\Persistent\Block\Header\Additional',
            [
                'context' => $this->contextMock,
                'customerViewHelper' => $this->customerViewHelperMock,
                'persistentSessionHelper' => $this->persistentSessionHelperMock,
                'customerRepository' => $this->customerRepositoryMock,
                'data' => []
            ]
        );
    }

    /**
     * Run test toHtml method
     *
     * @param bool $customerId
     * @return void
     *
     * @dataProvider dataProviderToHtml
     */
    public function testToHtml($customerId)
    {
        $cacheData = false;
        $idQueryParam = 'id-query-param';
        $sessionId = 'session-id';

        $this->additional->setData('cache_lifetime', 789);
        $this->additional->setData('cache_key', 'cache-key');

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('view_block_abstract_to_html_before', ['block' => $this->additional]);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'advanced/modules_disable_output/Magento_Persistent',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->willReturn(false);

        // get cache
        $this->cacheStateMock->expects($this->at(0))
            ->method('isEnabled')
            ->with(\Magento\Persistent\Block\Header\Additional::CACHE_GROUP)
            ->willReturn(true);
        // save cache
        $this->cacheStateMock->expects($this->at(1))
            ->method('isEnabled')
            ->with(\Magento\Persistent\Block\Header\Additional::CACHE_GROUP)
            ->willReturn(false);

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn($cacheData);
        $this->sidResolverMock->expects($this->never())
            ->method('getSessionIdQueryParam')
            ->with($this->sessionMock)
            ->willReturn($idQueryParam);
        $this->sessionMock->expects($this->never())
            ->method('getSessionId')
            ->willReturn($sessionId);

        // call protected _toHtml method
        $sessionMock = $this->getMock(
            'Magento\Persistent\Model\Session',
            ['getCustomerId'],
            [],
            '',
            false
        );

        $this->persistentSessionHelperMock->expects($this->atLeastOnce())
            ->method('getSession')
            ->willReturn($sessionMock);

        $sessionMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);

        if ($customerId) {
            $this->assertEquals('<span><a  >Not you?</a></span>', $this->additional->toHtml());
        } else {
            $this->assertEquals('', $this->additional->toHtml());
        }
    }

    /**
     * Data provider for dataProviderToHtml method
     *
     * @return array
     */
    public function dataProviderToHtml()
    {
        return [
            ['customerId' => 2],
            ['customerId' => null],
        ];
    }
}
