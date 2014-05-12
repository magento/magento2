<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $_expectedFieldsets = array('base_fieldset', 'upload_file_fieldset');

    /**
     * Add behaviour fieldsets to expected fieldsets
     *
     * @static
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $importModel = $objectManager->create('Magento\ImportExport\Model\Import');
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
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\ImportExport\Block\Adminhtml\Import\Edit\Form'
        );
        $prepareForm = new \ReflectionMethod('Magento\ImportExport\Block\Adminhtml\Import\Edit\Form', '_prepareForm');
        $prepareForm->setAccessible(true);
        $prepareForm->invoke($formBlock);

        // check form
        $form = $formBlock->getForm();
        $this->assertInstanceOf('Magento\Framework\Data\Form', $form, 'Incorrect import form class.');
        $this->assertTrue($form->getUseContainer(), 'Form should use container.');

        // check form fieldsets
        $formFieldsets = array();
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
                'Magento\Framework\Data\Form\Element\Fieldset',
                $fieldset,
                'Incorrect fieldset class.'
            );
        }
    }
}
