<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product\Attribute\Source\Price;

use Magento\Bundle\Model\Product\Attribute\Source\Price\View;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @var View
     */
    protected $model;

    /**
     * @var Option|MockObject
     */
    protected $option;

    /**
     * @var OptionFactory|MockObject
     */
    protected $optionFactory;

    /**
     * @var AbstractAttribute|MockObject
     */
    protected $attribute;

    protected function setUp(): void
    {
        $this->option = $this->createMock(Option::class);
        $this->optionFactory = $this->createPartialMock(
            OptionFactory::class,
            ['create']
        );
        $this->optionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->option);
        $this->attribute = $this->createMock(AbstractAttribute::class);

        $this->model = (new ObjectManager($this))
            ->getObject(
                View::class,
                [
                    'optionFactory' => $this->optionFactory,
                ]
            );
        $this->model->setAttribute($this->attribute);
    }

    public function testGetAllOptions()
    {
        $options = $this->model->getAllOptions();

        $this->assertIsArray($options);
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

        $this->assertInstanceOf(Phrase::class, $this->model->getOptionText($existValue));
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
            ->willReturn($code);

        $columns = $this->model->getFlatColumns();

        $this->assertIsArray($columns);
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
            ->willReturn($select);

        $this->assertEquals($select, $this->model->getFlatUpdateSelect($store));
    }
}
