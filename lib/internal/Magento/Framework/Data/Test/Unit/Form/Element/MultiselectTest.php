<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\Editablemultiselect;
use Magento\Framework\Data\Form\Element\Multiselect;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class MultiselectTest extends TestCase
{
    /**
     * @var Multiselect
     */
    protected $_model;

    protected function setUp(): void
    {
        $testHelper = new ObjectManager($this);
        $escaper = new Escaper();
        $this->_model = $testHelper->getObject(
            Editablemultiselect::class,
            [
                '_escaper' => $escaper
            ]
        );
        $this->_model->setForm(new DataObject());
    }

    /**
     * Verify that hidden input is present in multiselect.
     *
     * @covers \Magento\Framework\Data\Form\Element\Multiselect::getElementHtml
     * @return void
     */
    public function testHiddenFieldPresentInMultiSelect()
    {
        $fieldName = 'fieldName';
        $fieldId = 'fieldId';
        $this->_model->setCanBeEmpty(true);
        $this->_model->setName($fieldName);
        $this->_model->setId($fieldId);
        $elementHtml = $this->_model->getElementHtml();
        $this->assertStringContainsString(
            '<input type="hidden" id="' . $fieldId . '_hidden" name="' . $fieldName . '"',
            $elementHtml
        );
    }

    /**
     * Verify that hidden input is present in multiselect when multiselect is disabled.
     *
     * @return void
     */
    public function testHiddenDisabledFieldPresentInMultiSelect()
    {
        $fieldName = 'fieldName';
        $this->_model->setDisabled(true);
        $this->_model->setName($fieldName);
        $elementHtml = $this->_model->getElementHtml();
        $this->assertStringContainsString('<input type="hidden" name="' . $fieldName . '_disabled"', $elementHtml);
    }

    /**
     * Verify that hidden input is not present in multiselect when multiselect is not disabled.
     *
     * @covers \Magento\Framework\Data\Form\Element\Multiselect::getElementHtml
     * @return void
     */
    public function testHiddenDisabledFieldNotPresentInMultiSelect()
    {
        $fieldName = 'fieldName';
        $this->_model->setDisabled(false);
        $this->_model->setName($fieldName);
        $elementHtml = $this->_model->getElementHtml();
        $this->assertStringNotContainsString('<input type="hidden" name="' . $fieldName . '_disabled"', $elementHtml);
    }

    /**
     * Verify that js element is added.
     *
     * @return void
     */
    public function testGetAfterElementJs()
    {
        $this->_model->setAfterElementJs('<script language="text/javascript">var website = "website1";</script>');
        $elementHtml = $this->_model->getAfterElementJs();
        $this->assertStringContainsString('var website = "website1";', $elementHtml);
    }
}
