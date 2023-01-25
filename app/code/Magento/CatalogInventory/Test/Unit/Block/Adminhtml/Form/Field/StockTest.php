<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Block\Adminhtml\Form\Field;

use Magento\CatalogInventory\Block\Adminhtml\Form\Field\Stock;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\Data\Form\Element\TextFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class StockTest extends TestCase
{
    const ATTRIBUTE_NAME = 'quantity_and_stock_status';

    /**
     * @var Factory|MockObject
     */
    protected $_factoryElementMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $_collectionFactoryMock;

    /**
     * @var Text|MockObject
     */
    protected $_qtyMock;

    /**
     * @var TextFactory|MockObject
     */
    protected $_factoryTextMock;

    /**
     * @var Stock
     */
    protected $_block;

    protected function setUp(): void
    {
        $this->_factoryElementMock = $this->createMock(Factory::class);
        $this->_collectionFactoryMock = $this->createMock(
            CollectionFactory::class
        );
        $this->_qtyMock = $this->getMockBuilder(Text::class)
            ->addMethods(['setValue', 'setName'])
            ->onlyMethods(['setForm'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_factoryTextMock = $this->createMock(TextFactory::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->_block = $objectManagerHelper->getObject(
            Stock::class,
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
            $this->isInstanceOf(AbstractElement::class)
        );

        $objectManager = new ObjectManager($this);
        $this->_block->setForm(
            $objectManager->getObject(
                Text::class,
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
        $this->_qtyMock->expects($this->once())->method('setValue')->with(1);

        $this->_block->setValue($value);
    }

    public function testSetName()
    {
        $this->_qtyMock->expects($this->once())->method('setName')->with(self::ATTRIBUTE_NAME . '[qty]');

        $this->_block->setName(self::ATTRIBUTE_NAME);
    }
}
