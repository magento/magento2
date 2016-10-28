<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Block\Adminhtml\Export\Edit;

/**
 * Test class for block \Magento\ImportExport\Block\Adminhtml\Export\Edit\Form
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing model
     *
     * @var \Magento\ImportExport\Block\Adminhtml\Export\Edit\Form
     */
    protected $_model;

    /**
     * Expected form fieldsets and fields
     * array (
     *     <fieldset_id> => array(
     *         <element_id> => <element_name>,
     *         ...
     *     ),
     *     ...
     * )
     *
     * @var array
     */
    protected $_expectedFields = ['base_fieldset' => [
        'entity' => 'entity',
        'file_format' => 'file_format',
        'fields_enclosure' => 'fields_enclosure'
    ]];

    protected function setUp()
    {
        parent::setUp();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\ImportExport\Block\Adminhtml\Export\Edit\Form'
        );
    }

    /**
     * Test preparing of form
     *
     * @covers \Magento\ImportExport\Block\Adminhtml\Export\Edit\Form::_prepareForm
     */
    public function testPrepareForm()
    {
        // invoking _prepareForm
        $this->_model->toHtml();

        // get fieldset list
        $actualFieldsets = [];
        $formElements = $this->_model->getForm()->getElements();
        foreach ($formElements as $formElement) {
            if ($formElement instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
                $actualFieldsets[] = $formElement;
            }
        }

        // assert fieldsets and fields
        $this->assertSameSize($this->_expectedFields, $actualFieldsets);
        /** @var $actualFieldset \Magento\Framework\Data\Form\Element\Fieldset */
        foreach ($actualFieldsets as $actualFieldset) {
            $this->assertArrayHasKey($actualFieldset->getId(), $this->_expectedFields);
            $expectedFields = $this->_expectedFields[$actualFieldset->getId()];
            /** @var $actualField \Magento\Framework\Data\Form\Element\AbstractElement */
            foreach ($actualFieldset->getElements() as $actualField) {
                $this->assertArrayHasKey($actualField->getId(), $expectedFields);
                $this->assertEquals($expectedFields[$actualField->getId()], $actualField->getName());
            }
        }
    }
}
