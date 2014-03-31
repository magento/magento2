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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product attribute add form variations main tab
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\Edit\Tab\Variations;

class Main extends \Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain
{
    /**
     * Adding product form elements for editing attribute
     *
     * @return \Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\Edit\Tab\Variations\Main
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        /* @var $form \Magento\Data\Form */
        $form = $this->getForm();
        /* @var $fieldset \Magento\Data\Form\Element\Fieldset */
        $fieldset = $form->getElement('base_fieldset');
        $fieldsToRemove = array('attribute_code', 'is_unique', 'frontend_class');

        foreach ($fieldset->getElements() as $element) {
            /** @var \Magento\Data\Form\AbstractForm $element  */
            if (substr($element->getId(), 0, strlen('default_value')) == 'default_value') {
                $fieldsToRemove[] = $element->getId();
            }
        }
        foreach ($fieldsToRemove as $id) {
            $fieldset->removeField($id);
        }
        return $this;
    }
}
