<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Block\Product;

/**
 * Class ContextTest
 */
class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryMock;

    /**
     * @var \Magento\Catalog\Block\Product\Context
     */
    protected $context;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->stockRegistryMock = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockRegistryInterface',
            [],
            '',
            false
        );

        $this->context = $objectManager->getObject(
            'Magento\Catalog\Block\Product\Context',
            [
                'stockRegistry' => $this->stockRegistryMock
            ]
        );
    }

    /**
     * Run test getStockRegistry method
     *
     * @return void
     */
    public function testGetStockRegistry()
    {
        $this->assertEquals($this->stockRegistryMock, $this->context->getStockRegistry());
    }
}
