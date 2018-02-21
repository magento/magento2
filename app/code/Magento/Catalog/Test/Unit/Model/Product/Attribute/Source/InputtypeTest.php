<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class InputtypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Model\Product\Attribute\Source\Inputtype */
    protected $inputtypeModel;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    protected function setUp()
    {
        $this->registry = $this->getMock('Magento\Framework\Registry');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->inputtypeModel = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Product\Attribute\Source\Inputtype',
            [
                'coreRegistry' => $this->registry
            ]
        );
    }

    public function testToOptionArray()
    {
        $inputTypesSet = [
            ['value' => 'text', 'label' => 'Text Field'],
            ['value' => 'textarea', 'label' => 'Text Area'],
            ['value' => 'date', 'label' => 'Date'],
            ['value' => 'boolean', 'label' => 'Yes/No'],
            ['value' => 'multiselect', 'label' => 'Multiple Select'],
            ['value' => 'select', 'label' => 'Dropdown'],
            ['value' => 'price', 'label' => 'Price'],
            ['value' => 'media_image', 'label' => 'Media Image'],
        ];

        $this->registry->expects($this->once())->method('registry');
        $this->registry->expects($this->once())->method('register');
        $this->assertEquals($inputTypesSet, $this->inputtypeModel->toOptionArray());
    }
}
