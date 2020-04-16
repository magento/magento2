<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Block\Adminhtml\Product\Edit\Tab\Variations\Config;

/**
 * Class MatrixTest
 */
class MatrixTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Object under test
     *
     * @var \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Matrix
     */
    protected $_block;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockRegistryMock;

    protected function setUp(): void
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->stockRegistryMock = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\StockRegistryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStockItem']
        );

        $context = $objectHelper->getObject(
            \Magento\Backend\Block\Template\Context::class
        );
        $data = [
            'context' => $context,
            'formFactory' => $this->createMock(\Magento\Framework\Data\FormFactory::class),
            'productFactory' => $this->createMock(\Magento\Catalog\Model\ProductFactory::class),
            'stockRegistry' => $this->stockRegistryMock,
        ];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_object = $helper->getObject(\Magento\Config\Block\System\Config\Form::class, $data);
        $this->_block = $helper->getObject(
            \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Matrix::class,
            $data
        );
    }

    /**
     * Run test getProductStockQty method
     *
     * @return void
     */
    public function testGetProductStockQty()
    {
        $productId = 10;
        $websiteId = 99;
        $qty = 100.00;

        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getId', 'getStore']);
        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
        $stockItemMock = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\Data\StockItemInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getQty']
        );

        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId, $websiteId)
            ->willReturn($stockItemMock);
        $stockItemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($qty);

        $this->assertEquals($qty, $this->_block->getProductStockQty($productMock));
    }

    /**
     * @dataProvider getVariationWizardDataProvider
     * @param string $wizardBlockName
     * @param string $wizardHtml
     */
    public function testGetVariationWizard($wizardBlockName, $wizardHtml)
    {
        $initData = ['some-key' => 'some-value'];
        $wizardName = 'variation-steps-wizard';
        $blockConfig = [
            'config' => [
                'nameStepWizard' => $wizardName
            ]
        ];

        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $wizardBlock = $this->createMock(\Magento\Ui\Block\Component\StepsWizard::class);
        $layout->expects($this->any())->method('getChildName')->with(null, $wizardName)
            ->willReturn($wizardBlockName);
        $layout->expects($this->any())->method('getBlock')->with($wizardBlockName)->willReturn($wizardBlock);
        $wizardBlock->expects($this->any())->method('setInitData')->with($initData);
        $wizardBlock->expects($this->any())->method('toHtml')->willReturn($wizardHtml);

        $this->_block->setLayout($layout);
        $this->_block->setData($blockConfig);

        $this->assertEquals($wizardHtml, $this->_block->getVariationWizard($initData));
    }

    /**
     * @return array
     */
    public function getVariationWizardDataProvider()
    {
        return [['WizardBlockName', 'WizardHtml'], ['', '']];
    }
}
