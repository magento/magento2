<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use Magento\Catalog\Model\Product\Attribute\Source\Boolean as BooleanSource;

class BooleanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeFactoryMock;

    /**
     * @var BooleanSource
     */
    private $model;

    protected function setUp()
    {
        $this->attributeFactoryMock = $this->getMock(
            'Magento\Eav\Model\ResourceModel\Entity\AttributeFactory',
            [],
            [],
            '',
            false
        );
        $this->model = new BooleanSource($this->attributeFactoryMock);
    }

    public function testGetAllOptions()
    {
        $expectedResult = [
            ['label' => __('Yes'), 'value' => BooleanSource::VALUE_YES],
            ['label' => __('No'), 'value' => BooleanSource::VALUE_NO],
            ['label' => __('Use config'), 'value' => BooleanSource::VALUE_USE_CONFIG],
        ];
        $this->assertEquals($expectedResult, $this->model->getAllOptions());
    }
}
