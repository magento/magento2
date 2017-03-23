<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Product\Attribute\Source\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Product\Attribute\Source\Price\View
     */
    protected $model;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $option;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attribute;

    protected function setUp()
    {
        $this->option = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option::class,
            [],
            [],
            '',
            false
        );
        $this->optionFactory = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->optionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->option));
        $this->attribute = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            [],
            [],
            '',
            false
        );

        $this->model = (new ObjectManager($this))
            ->getObject(
                \Magento\Bundle\Model\Product\Attribute\Source\Price\View::class,
                [
                    'optionFactory' => $this->optionFactory,
                ]
            );
        $this->model->setAttribute($this->attribute);
    }

    public function testGetAllOptions()
    {
        $options = $this->model->getAllOptions();

        $this->assertInternalType('array', $options);
        $this->assertNotEmpty($options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('value', $option);
        }
    }

    /**
     * @covers \Magento\Bundle\Model\Product\Attribute\Source\Price\View::getOptionText
     */
    public function testGetOptionTextForExistLabel()
    {
        $existValue = 1;

        $this->assertInstanceOf(\Magento\Framework\Phrase::class, $this->model->getOptionText($existValue));
    }

    /**
     * @covers \Magento\Bundle\Model\Product\Attribute\Source\Price\View::getOptionText
     */
    public function testGetOptionTextForNotExistLabel()
    {
        $notExistValue = -1;

        $this->assertFalse($this->model->getOptionText($notExistValue));
    }

    public function testGetFlatColumns()
    {
        $code = 'attribute-code';
        $this->attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($code));

        $columns = $this->model->getFlatColumns();

        $this->assertInternalType('array', $columns);
        $this->assertArrayHasKey($code, $columns);

        foreach ($columns as $column) {
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertArrayHasKey('extra', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('nullable', $column);
            $this->assertArrayHasKey('comment', $column);
        }
    }

    public function testGetFlatUpdateSelect()
    {
        $store = 1;
        $select = 'select';

        $this->option->expects($this->once())
            ->method('getFlatUpdateSelect')
            ->with($this->attribute, $store, false)
            ->will($this->returnValue($select));

        $this->assertEquals($select, $this->model->getFlatUpdateSelect($store));
    }
}
