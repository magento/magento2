<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config;

/**
 * Class MatrixTest
 */
class MatrixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object under test
     *
     * @var \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix
     */
    protected $_block;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_appConfig;

    /** @var \Magento\Framework\Locale\CurrencyInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_locale;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryMock;

    protected function setUp()
    {
        $this->_appConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

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
            'Magento\Backend\Block\Template\Context',
            ['scopeConfig' => $this->_appConfig]
        );
        $this->_locale = $this->getMock('Magento\Framework\Locale\CurrencyInterface', [], [], '', false);
        $data = [
            'context' => $context,
            'localeCurrency' => $this->_locale,
            'formFactory' => $this->getMock('Magento\Framework\Data\FormFactory', [], [], '', false),
            'productFactory' => $this->getMock('Magento\Catalog\Model\ProductFactory', [], [], '', false),
            'stockRegistry' => $this->stockRegistryMock,
        ];
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_object = $helper->getObject('Magento\Backend\Block\System\Config\Form', $data);
        $this->_block = $helper->getObject(
            'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix',
            $data
        );
    }

    public function testRenderPrice()
    {
        $this->_appConfig->expects($this->once())->method('getValue')->will($this->returnValue('USD'));
        $currency = $this->getMock('Zend_Currency', [], [], '', false);
        $currency->expects($this->once())->method('toCurrency')->with('100.0000')->will($this->returnValue('$100.00'));
        $this->_locale->expects(
            $this->once()
        )->method(
            'getCurrency'
        )->with(
            'USD'
        )->will(
            $this->returnValue($currency)
        );
        $this->assertEquals('$100.00', $this->_block->renderPrice(100));
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
}
