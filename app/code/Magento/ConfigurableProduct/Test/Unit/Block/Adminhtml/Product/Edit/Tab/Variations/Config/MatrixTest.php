<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Block\Adminhtml\Product\Edit\Tab\Variations\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Matrix;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Store;
use Magento\Ui\Block\Component\StepsWizard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MatrixTest extends TestCase
{
    /**
     * Object under test
     *
     * @var Matrix
     */
    protected $_block;

    /**
     * @var StockRegistryInterface|MockObject
     */
    protected $stockRegistryMock;

    protected function setUp(): void
    {
        $objectHelper = new ObjectManager($this);

        $this->stockRegistryMock = $this->getMockForAbstractClass(
            StockRegistryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStockItem']
        );

        $context = $objectHelper->getObject(
            Context::class
        );
        $data = [
            'context' => $context,
            'formFactory' => $this->createMock(FormFactory::class),
            'productFactory' => $this->createMock(ProductFactory::class),
            'stockRegistry' => $this->stockRegistryMock,
        ];
        $helper = new ObjectManager($this);
        $this->_block = $helper->getObject(
            Matrix::class,
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

        $productMock = $this->createPartialMock(Product::class, ['getId', 'getStore']);
        $storeMock = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $stockItemMock = $this->getMockForAbstractClass(
            StockItemInterface::class,
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

        $layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $wizardBlock = $this->createMock(StepsWizard::class);
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
