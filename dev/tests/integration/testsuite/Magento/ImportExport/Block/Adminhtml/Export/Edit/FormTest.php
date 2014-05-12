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
    protected $_expectedFields = array('base_fieldset' => array('entity' => 'entity', 'file_format' => 'file_format'));

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
        $actualFieldsets = array();
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
