<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Block\Adminhtml\Form\Field;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class StockTest extends \PHPUnit\Framework\TestCase
{
    const ATTRIBUTE_NAME = 'quantity_and_stock_status';

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryElementMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\CollectionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionFactoryMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\Text|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_qtyMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\TextFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryTextMock;

    /**
     * @var \Magento\CatalogInventory\Block\Adminhtml\Form\Field\Stock
     */
    protected $_block;

    protected function setUp()
    {
        $this->_factoryElementMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $this->_collectionFactoryMock = $this->createMock(
            \Magento\Framework\Data\Form\Element\CollectionFactory::class
        );
        $this->_qtyMock = $this->createPartialMock(
            \Magento\Framework\Data\Form\Element\Text::class,
            ['setForm', 'setValue', 'setName']
        );
        $this->_factoryTextMock = $this->createMock(\Magento\Framework\Data\Form\Element\TextFactory::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_block = $objectManagerHelper->getObject(
            \Magento\CatalogInventory\Block\Adminhtml\Form\Field\Stock::class,
            [
                'factoryElement' => $this->_factoryElementMock,
                'factoryCollection' => $this->_collectionFactoryMock,
                'factoryText' => $this->_factoryTextMock,
                'data' => ['qty' => $this->_qtyMock, 'name' => self::ATTRIBUTE_NAME]
            ]
        );
    }

    public function testSetForm()
    {
        $this->_qtyMock->expects(
            $this->once()
        )->method(
            'setForm'
        )->with(
            $this->isInstanceOf(\Magento\Framework\Data\Form\Element\AbstractElement::class)
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_block->setForm(
            $objectManager->getObject(
                \Magento\Framework\Data\Form\Element\Text::class,
                [
                    'factoryElement' => $this->_factoryElementMock,
                    'factoryCollection' => $this->_collectionFactoryMock
                ]
            )
        );
    }

    public function testSetValue()
    {
        $value = ['qty' => 1, 'is_in_stock' => 0];
        $this->_qtyMock->expects($this->once())->method('setValue')->with($this->equalTo(1));

        $this->_block->setValue($value);
    }

    public function testSetName()
    {
        $this->_qtyMock->expects($this->once())->method('setName')->with(self::ATTRIBUTE_NAME . '[qty]');

        $this->_block->setName(self::ATTRIBUTE_NAME);
    }
}
