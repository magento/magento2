<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Billing;

class AgreementsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @codingStandardsIgnoreStart
     * @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     * @codingStandardsIgnoreEnd
     */
    private $agreementCollection;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaper;

    /**
     * @var \Magento\Paypal\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layout;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \Magento\Paypal\Block\Billing\Agreements
     */
    private $block;

    protected function setUp()
    {
        $this->context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->escaper = $this->getMock('Magento\Framework\Escaper', [], [], '', false);
        $this->context->expects($this->once())->method('getEscaper')->willReturn($this->escaper);
        $localeDate = $this->getMockForAbstractClass(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface',
            [],
            '',
            false
        );
        $this->context->expects($this->once())->method('getLocaleDate')->willReturn($localeDate);
        $this->urlBuilder = $this->getMockForAbstractClass('Magento\Framework\UrlInterface', [], '', false);
        $this->context->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->layout = $this->getMockForAbstractClass('Magento\Framework\View\LayoutInterface', [], '', false);
        $this->context->expects($this->once())->method('getLayout')->willReturn($this->layout);
        $this->eventManager = $this->getMockForAbstractClass('Magento\Framework\Event\ManagerInterface', [], '', false);
        $this->context->expects($this->once())->method('getEventManager')->willReturn($this->eventManager);
        $this->scopeConfig = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false
        );
        $this->context->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfig);
        $this->cache = $this->getMockForAbstractClass('Magento\Framework\App\CacheInterface', [], '', false);
        $this->context->expects($this->once())->method('getCache')->willReturn($this->cache);
        $this->agreementCollection = $this->getMockBuilder(
            'Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->helper = $this->getMock('Magento\Paypal\Helper\Data', [], [], '', false);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->block = $objectManager->getObject(
            'Magento\Paypal\Block\Billing\Agreements',
            [
                'context' => $this->context,
                'agreementCollection' => $this->agreementCollection,
                'helper' => $this->helper,
            ]
        );
    }

    public function testGetBillingAgreements()
    {
        $collection = $this->getMock(
            'Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection',
            [],
            [],
            '',
            false
        );
        $this->agreementCollection->expects($this->once())->method('create')->willReturn($collection);
        $collection->expects($this->once())->method('addFieldToFilter')->willReturn($collection);
        $collection->expects($this->once())->method('setOrder')->willReturn($collection);
        $this->assertSame($collection, $this->block->getBillingAgreements());
        // call second time to make sure mock only called once
        $this->block->getBillingAgreements();
    }

    public function testGetItemValueCreatedAt()
    {
        $this->escaper->expects($this->once())->method('escapeHtml');
        $item = $this->getMock('Magento\Paypal\Model\Billing\Agreement', [], [], '', false);
        $item->expects($this->exactly(2))->method('getData')->with('created_at')->willReturn('03/10/2014');
        $this->block->getItemValue($item, 'created_at');
    }

    public function testGetItemValueCreatedAtNoData()
    {
        $this->escaper->expects($this->once())->method('escapeHtml');
        $item = $this->getMock('Magento\Paypal\Model\Billing\Agreement', [], [], '', false);
        $item->expects($this->once())->method('getData')->with('created_at')->willReturn(false);
        $this->block->getItemValue($item, 'created_at');
    }

    public function testGetItemValueUpdatedAt()
    {
        $this->escaper->expects($this->once())->method('escapeHtml');
        $item = $this->getMock('Magento\Paypal\Model\Billing\Agreement', [], [], '', false);
        $item->expects($this->exactly(2))->method('getData')->with('updated_at')->willReturn('03/10/2014');
        $this->block->getItemValue($item, 'updated_at');
    }

    public function testGetItemValueUpdatedAtNoData()
    {
        $this->escaper->expects($this->once())->method('escapeHtml');
        $item = $this->getMock('Magento\Paypal\Model\Billing\Agreement', [], [], '', false);
        $item->expects($this->once())->method('getData')->with('updated_at')->willReturn(false);
        $this->block->getItemValue($item, 'updated_at');
    }

    public function testGetItemValueEditUrl()
    {
        $this->escaper->expects($this->once())->method('escapeHtml');
        $item = $this->getMock('Magento\Paypal\Model\Billing\Agreement', ['getAgreementId'], [], '', false);
        $item->expects($this->once())->method('getAgreementId')->willReturn(1);
        $this->urlBuilder
            ->expects($this->once())
            ->method('getUrl')
            ->with('paypal/billing_agreement/view', ['agreement' => 1]);
        $this->block->getItemValue($item, 'edit_url');
    }

    public function testGetItemPaymentMethodLabel()
    {
        $this->escaper->expects($this->once())->method('escapeHtml')->with('label', null);
        $item = $this->getMock('Magento\Paypal\Model\Billing\Agreement', ['getAgreementLabel'], [], '', false);
        $item->expects($this->once())->method('getAgreementLabel')->willReturn('label');
        $this->block->getItemValue($item, 'payment_method_label');
    }

    public function testGetItemStatus()
    {
        $this->escaper->expects($this->once())->method('escapeHtml')->with('status', null);
        $item = $this->getMock('Magento\Paypal\Model\Billing\Agreement', [], [], '', false);
        $item->expects($this->once())->method('getStatusLabel')->willReturn('status');
        $this->block->getItemValue($item, 'status');
    }

    public function testGetItemDefault()
    {
        $this->escaper->expects($this->once())->method('escapeHtml')->with('value', null);
        $item = $this->getMock('Magento\Paypal\Model\Billing\Agreement', [], [], '', false);
        $item->expects($this->exactly(2))->method('getData')->with('default')->willReturn('value');
        $this->block->getItemValue($item, 'default');
    }

    public function testGetWizardPaymentMethodOptions()
    {
        $method1 = $this->getMock(
            'Magento\Paypal\Model\Method\Agreement',
            ['getConfigData', 'getCode', 'getTitle'],
            [],
            '',
            false
        );
        $method2 = $this->getMock(
            'Magento\Paypal\Model\Method\Agreement',
            ['getConfigData', 'getCode', 'getTitle'],
            [],
            '',
            false
        );
        $method3 = $this->getMock(
            'Magento\Paypal\Model\Method\Agreement',
            ['getConfigData', 'getCode', 'getTitle'],
            [],
            '',
            false
        );
        $method1->expects($this->once())->method('getCode')->willReturn('code1');
        $method2->expects($this->never())->method('getCode');
        $method3->expects($this->once())->method('getCode')->willReturn('code3');
        $method1->expects($this->once())->method('getTitle')->willReturn('title1');
        $method2->expects($this->never())->method('getTitle');
        $method3->expects($this->once())->method('getTitle')->willReturn('title3');
        $method1->expects($this->any())->method('getConfigData')->willReturn(1);
        $method2->expects($this->any())->method('getConfigData')->willReturn(0);
        $method3->expects($this->any())->method('getConfigData')->willReturn(1);
        $paymentMethods = [$method1, $method2, $method3];
        $this->helper->expects($this->once())->method('getBillingAgreementMethods')->willReturn($paymentMethods);
        $this->assertEquals(['code1' => 'title1', 'code3' => 'title3'], $this->block->getWizardPaymentMethodOptions());
    }

    public function testToHtml()
    {
        $this->eventManager
            ->expects($this->once())
            ->method('dispatch')
            ->with('view_block_abstract_to_html_before', ['block' => $this->block]);
        $this->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);
        $this->urlBuilder->expects($this->once())->method('getUrl')->with('paypal/billing_agreement/startWizard', []);
        $this->block->toHtml();
    }
}
