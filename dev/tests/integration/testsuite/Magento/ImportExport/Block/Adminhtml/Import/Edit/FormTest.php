<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Block\Adminhtml\Import\Edit;

/**
 * Tests for block \Magento\ImportExport\Block\Adminhtml\Import\Edit\FormTest
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * List of expected fieldsets in import edit form
     *
     * @var array
     */
    protected $_expectedFieldsets = ['base_fieldset', 'upload_file_fieldset'];

    /**
     * Add behaviour fieldsets to expected fieldsets
     *
     * @static
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $importModel = $objectManager->create(\Magento\ImportExport\Model\Import::class);
        $uniqueBehaviors = $importModel->getUniqueEntityBehaviors();
        foreach (array_keys($uniqueBehaviors) as $behavior) {
            $this->_expectedFieldsets[] = $behavior . '_fieldset';
        }
    }

    /**
     * Test content of form after _prepareForm
     */
    public function testPrepareForm()
    {
        $formBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\ImportExport\Block\Adminhtml\Import\Edit\Form::class
        );
        $prepareForm = new \ReflectionMethod(
            \Magento\ImportExport\Block\Adminhtml\Import\Edit\Form::class,
            '_prepareForm'
        );
        $prepareForm->setAccessible(true);
        $prepareForm->invoke($formBlock);

        // check form
        $form = $formBlock->getForm();
        $this->assertInstanceOf(\Magento\Framework\Data\Form::class, $form, 'Incorrect import form class.');
        $this->assertTrue($form->getUseContainer(), 'Form should use container.');

        // check form fieldsets
        $formFieldsets = [];
        $formElements = $form->getElements();
        foreach ($formElements as $element) {
            /** @var $element \Magento\Framework\Data\Form\Element\AbstractElement */
            if (in_array($element->getId(), $this->_expectedFieldsets)) {
                $formFieldsets[] = $element;
            }
        }
        $this->assertSameSize($this->_expectedFieldsets, $formFieldsets);
        foreach ($formFieldsets as $fieldset) {
            $this->assertInstanceOf(
                \Magento\Framework\Data\Form\Element\Fieldset::class,
                $fieldset,
                'Incorrect fieldset class.'
            );
        }
    }
}
