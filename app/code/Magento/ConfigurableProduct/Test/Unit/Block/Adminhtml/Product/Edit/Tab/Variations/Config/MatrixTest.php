<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Block\Adminhtml\Product\Edit\Tab\Variations\Config;

/**
 * Class MatrixTest
 */
class MatrixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object under test
     *
     * @var \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Matrix
     */
    protected $_block;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryMock;

    protected function setUp()
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->stockRegistryMock = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockRegistryInterface',
            [],
            '',
            false,
            true,
            true,
            ['getStockItem']
        );

        $context = $objectHelper->getObject(
            'Magento\Backend\Block\Template\Context'
        );
        $data = [
            'context' => $context,
            'formFactory' => $this->getMock('Magento\Framework\Data\FormFactory', [], [], '', false),
            'productFactory' => $this->getMock('Magento\Catalog\Model\ProductFactory', [], [], '', false),
            'stockRegistry' => $this->stockRegistryMock,
        ];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_object = $helper->getObject('Magento\Config\Block\System\Config\Form', $data);
        $this->_block = $helper->getObject(
            'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Matrix',
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

        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getId', 'getStore'],
            [],
            '',
            false
        );
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['getWebsiteId'],
            [],
            '',
            false
        );
        $stockItemMock = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\Data\StockItemInterface',
            [],
            '',
            false,
            true,
            true,
            ['getQty']
        );

        $productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));
        $productMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId, $websiteId)
            ->will($this->returnValue($stockItemMock));
        $stockItemMock->expects($this->once())
            ->method('getQty')
            ->will($this->returnValue($qty));

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

        $layout = $this->getMock('Magento\Framework\View\LayoutInterface');
        $wizardBlock = $this->getMock('Magento\Ui\Block\Component\StepsWizard', [], [], '', false);
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
