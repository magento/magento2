<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Billing;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\View\LayoutInterface;
use Magento\Paypal\Block\Billing\Agreements;
use Magento\Paypal\Helper\Data;
use Magento\Paypal\Model\Billing\Agreement;
use Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection;
use Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AgreementsTest extends TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Context|MockObject
     */
    private $context;

    /**
     * @codingStandardsIgnoreStart
     * @var CollectionFactory|MockObject
     * @codingStandardsIgnoreEnd
     */
    private $agreementCollection;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /**
     * @var Data|MockObject
     */
    private $helper;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layout;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManager;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var CacheInterface|MockObject
     */
    private $cache;

    /**
     * @var Agreements
     */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(TemplateContext::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->context->expects($this->once())->method('getEscaper')->willReturn($this->escaper);
        $localeDate = $this->getMockForAbstractClass(
            TimezoneInterface::class,
            [],
            '',
            false
        );
        $this->context->expects($this->once())->method('getLocaleDate')->willReturn($localeDate);
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class, [], '', false);
        $this->context->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->layout = $this->getMockForAbstractClass(LayoutInterface::class, [], '', false);
        $this->context->expects($this->once())->method('getLayout')->willReturn($this->layout);
        $this->eventManager = $this->getMockForAbstractClass(
            ManagerInterface::class,
            [],
            '',
            false
        );
        $this->context->expects($this->once())->method('getEventManager')->willReturn($this->eventManager);
        $this->scopeConfig = $this->getMockForAbstractClass(
            ScopeConfigInterface::class,
            [],
            '',
            false
        );
        $this->context->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfig);
        $this->cache = $this->getMockForAbstractClass(CacheInterface::class, [], '', false);
        $this->context->expects($this->once())->method('getCache')->willReturn($this->cache);
        $this->agreementCollection = $this->getMockBuilder(
            CollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();
        $this->helper = $this->createMock(Data::class);
        $objectManager = new ObjectManager($this);

        $this->block = $objectManager->getObject(
            Agreements::class,
            [
                'context' => $this->context,
                'agreementCollection' => $this->agreementCollection,
                'helper' => $this->helper,
            ]
        );
    }

    public function testGetBillingAgreements()
    {
        $collection = $this->createMock(Collection::class);
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
        $item = $this->createMock(Agreement::class);
        $item->expects($this->exactly(2))->method('getData')->with('created_at')->willReturn('03/10/2014');
        $this->block->getItemValue($item, 'created_at');
    }

    public function testGetItemValueCreatedAtNoData()
    {
        $this->escaper->expects($this->once())->method('escapeHtml');
        $item = $this->createMock(Agreement::class);
        $item->expects($this->once())->method('getData')->with('created_at')->willReturn(false);
        $this->block->getItemValue($item, 'created_at');
    }

    public function testGetItemValueUpdatedAt()
    {
        $this->escaper->expects($this->once())->method('escapeHtml');
        $item = $this->createMock(Agreement::class);
        $item->expects($this->exactly(2))->method('getData')->with('updated_at')->willReturn('03/10/2014');
        $this->block->getItemValue($item, 'updated_at');
    }

    public function testGetItemValueUpdatedAtNoData()
    {
        $this->escaper->expects($this->once())->method('escapeHtml');
        $item = $this->createMock(Agreement::class);
        $item->expects($this->once())->method('getData')->with('updated_at')->willReturn(false);
        $this->block->getItemValue($item, 'updated_at');
    }

    public function testGetItemValueEditUrl()
    {
        $this->escaper->expects($this->once())->method('escapeHtml');
        $item = $this->getMockBuilder(Agreement::class)
            ->addMethods(['getAgreementId'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $item = $this->getMockBuilder(Agreement::class)
            ->addMethods(['getAgreementLabel'])
            ->disableOriginalConstructor()
            ->getMock();
        $item->expects($this->once())->method('getAgreementLabel')->willReturn('label');
        $this->block->getItemValue($item, 'payment_method_label');
    }

    public function testGetItemStatus()
    {
        $this->escaper->expects($this->once())->method('escapeHtml')->with('status', null);
        $item = $this->createMock(Agreement::class);
        $item->expects($this->once())->method('getStatusLabel')->willReturn('status');
        $this->block->getItemValue($item, 'status');
    }

    public function testGetItemDefault()
    {
        $this->escaper->expects($this->once())->method('escapeHtml')->with('value', null);
        $item = $this->createMock(Agreement::class);
        $item->expects($this->exactly(2))->method('getData')->with('default')->willReturn('value');
        $this->block->getItemValue($item, 'default');
    }

    public function testGetWizardPaymentMethodOptions()
    {
        $method1 = $this->createPartialMock(
            \Magento\Paypal\Model\Method\Agreement::class,
            ['getConfigData', 'getCode', 'getTitle']
        );
        $method2 = $this->createPartialMock(
            \Magento\Paypal\Model\Method\Agreement::class,
            ['getConfigData', 'getCode', 'getTitle']
        );
        $method3 = $this->createPartialMock(
            \Magento\Paypal\Model\Method\Agreement::class,
            ['getConfigData', 'getCode', 'getTitle']
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
            ->expects($this->at(0))
            ->method('dispatch')
            ->with('view_block_abstract_to_html_before', ['block' => $this->block]);
        $transport = new DataObject(['html' => '']);
        $this->eventManager
            ->expects($this->at(1))
            ->method('dispatch')
            ->with('view_block_abstract_to_html_after', ['block' => $this->block, 'transport' => $transport]);
        $this->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);
        $this->urlBuilder->expects($this->once())->method('getUrl')->with('paypal/billing_agreement/startWizard', []);
        $this->block->toHtml();
    }
}
