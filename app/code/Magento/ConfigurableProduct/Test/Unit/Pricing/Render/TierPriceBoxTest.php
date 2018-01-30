<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Render;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Tests \Magento\ConfigurableProduct\Pricing\Render\TierPriceBox.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceBoxTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\ConfigurableProduct\Pricing\Render\TierPriceBox */
    private $priceBox;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /** @var \Magento\Framework\View\Element\Template\Context||\PHPUnit_Framework_MockObject_MockObject */
    private $contextMock;

    /** @var \Magento\Catalog\Model\Product||\PHPUnit_Framework_MockObject_MockObject */
    private $saleableItemMock;

    /** @var \Magento\Framework\Pricing\Price\PriceInterface||\PHPUnit_Framework_MockObject_MockObject */
    private $priceMock;

    /** @var \Magento\Framework\Pricing\Render\RendererPool||\PHPUnit_Framework_MockObject_MockObject */
    private $rendererPoolMock;

    /** @var ConfigurableOptionsProviderInterface||\PHPUnit_Framework_MockObject_MockObject */
    private $configurableOptionsProviderMock;

    /** @var LowestPriceOptionsProviderInterface||\PHPUnit_Framework_MockObject_MockObject */
    private $lowestPriceOptionsProviderMock;

    /** @var  \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject */
    private $moduleManagerMock;

    /** @var SalableResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $salableResolverMock;

    protected function setUp()
    {
        $eventManager = $this->getMock(\Magento\Framework\Event\Test\Unit\ManagerStub::class, [], [], '', false);
        $scopeConfigMock = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $this->contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfigMock));

        $this->saleableItemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getCanShowPrice', 'getPriceInfo'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceMock = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->rendererPoolMock = $this->getMockBuilder(\Magento\Framework\Pricing\Render\RendererPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurableOptionsProviderMock = $this->getMockBuilder(ConfigurableOptionsProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->lowestPriceOptionsProviderMock = $this->getMockBuilder(LowestPriceOptionsProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->salableResolverMock = $this->getMockBuilder(SalableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleManagerMock = $this->getMockBuilder(\Magento\Framework\Module\Manager::class)
            ->setMethods(['isEnabled', 'isOutputEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject $objectManagerMock */
        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManagerMock->expects(self::at(0))->method('get')->with(SalableResolverInterface::class)
            ->willReturn($this->salableResolverMock);
        $objectManagerMock->expects(self::at(1))->method('get')->with(\Magento\Framework\Module\Manager::class)
            ->willReturn($this->moduleManagerMock);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->priceBox = $this->objectManager->getObject(
            \Magento\ConfigurableProduct\Pricing\Render\TierPriceBox::class,
            [
                'context' => $this->contextMock,
                'saleableItem' => $this->saleableItemMock,
                'price' => $this->priceMock,
                'rendererPool' => $this->rendererPoolMock,
                'configurableOptionsProvider' => $this->configurableOptionsProviderMock,
                'data' => [],
                'lowestPriceOptionsProvider' => $this->lowestPriceOptionsProviderMock,
            ]
        );
    }

    /**
     * Covers toHtml() with Msrp module disabled.
     *
     * @return void
     */
    public function testToHtmlMsrpDisabled()
    {
        $this->saleableItemMock->expects($this->any())
            ->method('getCanShowPrice')
            ->willReturn(true);
        $this->priceMock->expects($this->any())
            ->method('getPriceCode')
            ->will($this->returnValue(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE));
        $this->moduleManagerMock->expects($this->any())
            ->method('isEnabled')
            ->with('Magento_Msrp')
            ->willReturn(false);

        $result = $this->priceBox->toHtml();

        $this->assertStringStartsWith('<div', $result);
    }

    /**
     * Covers toHtml() with Msrp module enabled.
     *
     * @return void
     */
    public function testToHtmlMsrpEnabled()
    {
        $this->moduleManagerMock->expects($this->any())
            ->method('isEnabled')
            ->with('Magento_Msrp')
            ->willReturn(true);
        $this->moduleManagerMock->expects($this->any())
            ->method('isOutputEnabled')
            ->with('Magento_Msrp')
            ->willReturn(true);
        $priceInterfaceMock = $this->getMockBuilder(Magento\Framework\Pricing\Price\PriceInterface::class)
            ->setMethods(['canApplyMsrp', 'isMinimalPriceLessMsrp'])
            ->disableOriginalConstructor()
            ->getMock();
        $priceInterfaceMock->expects($this->once())
            ->method('canApplyMsrp')
            ->with($this->saleableItemMock)
            ->willReturn(true);
        $priceInterfaceMock->expects($this->once())
            ->method('isMinimalPriceLessMsrp')
            ->with($this->saleableItemMock)
            ->willReturn(true);
        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfoInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with('msrp_price')
            ->willReturn($priceInterfaceMock);
        $this->saleableItemMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $this->assertEmpty($this->priceBox->toHtml());
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        // reset ObjectManager instance.
        $reflection = new \ReflectionClass(\Magento\Framework\App\ObjectManager::class);
        $reflectionProperty = $reflection->getProperty('_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, null);
    }
}
