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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

/**
 * Generic block that uses customer metatdata attributes.
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class GenericMetadata extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Set Fieldset to Form
     *
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[] $attributes attributes that are to be added
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $exclude attributes that should be skipped
     * @return void
     */
    protected function _setFieldset($attributes, $fieldset, $exclude = array())
    {
        $this->_addElementTypes($fieldset);

        foreach ($attributes as $attribute) {
            // Note, ignoring whether its visible or not,
            if (($inputType = $attribute->getFrontendInput()) && !in_array(
                $attribute->getAttributeCode(),
                $exclude
            ) && ('media_image' != $inputType || $attribute->getAttributeCode() == 'image')
            ) {

                $fieldType = $inputType;
                $element = $fieldset->addField(
                    $attribute->getAttributeCode(),
                    $fieldType,
                    array(
                        'name' => $attribute->getAttributeCode(),
                        'label' => __($attribute->getFrontendLabel()),
                        'class' => $attribute->getFrontendClass(),
                        'required' => $attribute->isRequired(),
                        'note' => $attribute->getNote()
                    )
                );

                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                $this->_applyTypeSpecificConfigCustomer($inputType, $element, $attribute);
            }
        }
    }

    /**
     * Apply configuration specific for different element type
     *
     * @param string $inputType
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute
     * @return void
     */
    protected function _applyTypeSpecificConfigCustomer(
        $inputType,
        $element,
        \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute
    ) {
        switch ($inputType) {
            case 'select':
                $element->setValues($this->_getAttributeOptionsArray($attribute));
                break;
            case 'multiselect':
                $element->setValues($this->_getAttributeOptionsArray($attribute));
                $element->setCanBeEmpty(true);
                break;
            case 'date':
                $element->setImage($this->getViewFileUrl('images/grid-cal.gif'));
                $element->setDateFormat($this->_localeDate->getDateFormatWithLongYear());
                break;
            case 'multiline':
                $element->setLineCount($attribute->getMultilineCount());
                break;
            default:
                break;
        }
    }

    /**
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute
     * @return array
     */
    protected function _getAttributeOptionsArray(\Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute)
    {
        $options = $attribute->getOptions();
        $result = array();
        foreach ($options as $option) {
            $result[] = $option->__toArray();
        }
        return $result;
    }
}
