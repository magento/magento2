<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use Magento\Catalog\Model\Product\Attribute\Source\Inputtype;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InputtypeTest extends TestCase
{
    /** @var Inputtype */
    protected $inputtypeModel;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Registry|MockObject */
    protected $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(Registry::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->inputtypeModel = $this->objectManagerHelper->getObject(
            Inputtype::class,
            [
                'coreRegistry' => $this->registry,
                'optionsArray' => $this->getInputTypeSet()
            ]
        );
    }

    public function testToOptionArray()
    {
        $extraValues = [
            ['value' => 'price', 'label' => 'Price'],
            ['value' => 'media_image', 'label' => 'Media Image']
        ];
        $inputTypesSet = $this->getInputTypeSet();
        $inputTypesSet = array_merge($inputTypesSet, $extraValues);

        $this->registry->expects($this->once())->method('registry');
        $this->registry->expects($this->once())->method('register');
        $this->assertEquals($inputTypesSet, $this->inputtypeModel->toOptionArray());
    }

    /**
     * @return array
     */
    private function getInputTypeSet()
    {
        return [
            ['value' => 'text', 'label' => 'Text Field'],
            ['value' => 'textarea', 'label' => 'Text Area'],
            ['value' => 'texteditor', 'label' => 'Text Editor'],
            ['value' => 'date', 'label' => 'Date'],
            ['value' => 'boolean', 'label' => 'Yes/No'],
            ['value' => 'multiselect', 'label' => 'Multiple Select'],
            ['value' => 'select', 'label' => 'Dropdown']
        ];
    }
}
