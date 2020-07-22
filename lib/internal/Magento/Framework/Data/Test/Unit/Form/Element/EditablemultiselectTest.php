<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\Editablemultiselect;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class EditablemultiselectTest extends TestCase
{
    /**
     * @var Editablemultiselect
     */
    protected $_model;

    protected function setUp(): void
    {
        $testHelper = new ObjectManager($this);
        $this->_model = $testHelper->getObject(Editablemultiselect::class);
        $values = [
            ['value' => 1, 'label' => 'Value1'],
            ['value' => 2, 'label' => 'Value2'],
            ['value' => 3, 'label' => 'Value3'],
        ];
        $value = [1, 3];
        $this->_model->setForm(new DataObject());
        $this->_model->setData(['values' => $values, 'value' => $value]);
    }

    public function testGetElementHtmlRendersDataAttributesWhenDisabled()
    {
        $this->_model->setDisabled(true);
        $elementHtml = $this->_model->getElementHtml();
        $this->assertStringContainsString('disabled="disabled"', $elementHtml);
        $this->assertStringContainsString('data-is-removable="no"', $elementHtml);
        $this->assertStringContainsString('data-is-editable="no"', $elementHtml);
    }

    public function testGetElementHtmlRendersRelatedJsClassInitialization()
    {
        $this->_model->setElementJsClass('CustomSelect');
        $elementHtml = $this->_model->getElementHtml();
        $this->assertStringContainsString('ElementControl = new CustomSelect(', $elementHtml);
        $this->assertStringContainsString('ElementControl.init();', $elementHtml);
    }
}
