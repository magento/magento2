<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Plugin\Website
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_priceProcessorMock;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_priceProcessorMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Price\Processor',
            ['markIndexerAsInvalid'],
            [],
            '',
            false
        );

        $this->_model = $this->_objectManager->getObject(
            'Magento\Catalog\Model\Indexer\Product\Price\Plugin\Website',
            ['processor' => $this->_priceProcessorMock]
        );
    }

    public function testAfterDelete()
    {
        $this->_priceProcessorMock->expects($this->once())->method('markIndexerAsInvalid');

        $websiteMock = $this->getMock('Magento\Store\Model\Resource\Website', [], [], '', false);
        $this->assertEquals('return_value', $this->_model->afterDelete($websiteMock, 'return_value'));
    }
}
