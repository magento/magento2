<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field\FieldArray;

class AbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     */
    private $model;

    protected function setUp()
    {
        $this->model = $this->getMockForAbstractClass(
            \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray::class,
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
        $this->model->expects($this->any())->method('escapeHtml')->will($this->returnArgument(0));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $element = $objectManager->getObject(\Magento\Framework\Data\Form\Element\Multiselect::class);
        $element->setValue([['te<s>t' => 't<e>st', 'data&1' => 'da&ta1']]);
        $this->model->setElement($element);
        $this->assertEquals(
            [
                new \Magento\Framework\DataObject(
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
        $contextMock = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model->__construct($contextMock);

        $this->assertEquals("Add", $this->model->getAddButtonLabel());
    }
}
