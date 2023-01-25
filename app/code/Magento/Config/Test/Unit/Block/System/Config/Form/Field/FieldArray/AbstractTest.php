<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field\FieldArray;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\Multiselect;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class AbstractTest extends TestCase
{
    /**
     * @var AbstractFieldArray
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = $this->getMockForAbstractClass(
            AbstractFieldArray::class,
            [],
            '',
            false,
            true,
            true,
            ['escapeHtml']
        );
    }

    public function testGetArrayRows()
    {
        $this->model->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $objectManager = new ObjectManager($this);
        $element = $objectManager->getObject(Multiselect::class);
        $element->setValue([['te<s>t' => 't<e>st', 'data&1' => 'da&ta1']]);
        $this->model->setElement($element);
        $this->assertEquals(
            [
                new DataObject(
                    [
                        'te<s>t' => 't<e>st',
                        'data&1' => 'da&ta1',
                        '_id' => 0,
                        'column_values' => ['0_te<s>t' => 't<e>st', '0_data&1' => 'da&ta1'],
                    ]
                ),
            ],
            $this->model->getArrayRows()
        );
    }

    public function testGetAddButtonLabel()
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model->__construct($contextMock);

        $this->assertEquals("Add", $this->model->getAddButtonLabel());
    }
}
