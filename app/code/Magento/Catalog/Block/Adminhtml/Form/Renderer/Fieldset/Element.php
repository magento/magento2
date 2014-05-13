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

/**
 * Catalog fieldset element renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset;

class Element extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * Initialize block template
     */
    protected $_template = 'Magento_Catalog::catalog/form/renderer/fieldset/element.phtml';

    /**
     * Retrieve data object related with form
     *
     * @return \Magento\Catalog\Model\Product || \Magento\Catalog\Model\Category
     */
    public function getDataObject()
    {
        return $this->getElement()->getForm()->getDataObject();
    }

    /**
     * Retireve associated with element attribute object
     *
     * @return \Magento\Catalog\Model\Resource\Eav\Attribute
     */
    public function getAttribute()
    {
        return $this->getElement()->getEntityAttribute();
    }

    /**
     * Retrieve associated attribute code
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->getAttribute()->getAttributeCode();
    }

    /**
     * Check "Use default" checkbox display availability
     *
     * @return bool
     */
    public function canDisplayUseDefault()
    {
        if ($attribute = $this->getAttribute()) {
            if (!$attribute->isScopeGlobal() &&
                $this->getDataObject() &&
                $this->getDataObject()->getId() &&
                $this->getDataObject()->getStoreId()
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check default value usage fact
     *
     * @return bool
     */
    public function usedDefault()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $defaultValue = $this->getDataObject()->getAttributeDefaultValue($attributeCode);

        if (!$this->getDataObject()->getExistsStoreValueFlag($attributeCode)) {
            return true;
        } else if ($this->getElement()->getValue() == $defaultValue &&
            $this->getDataObject()->getStoreId() != $this->_getDefaultStoreId()
        ) {
            return false;
        }
        if ($defaultValue === false && !$this->getAttribute()->getIsRequired() && $this->getElement()->getValue()) {
            return false;
        }
        return $defaultValue === false;
    }

    /**
     * Disable field in default value using case
     *
     * @return \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
     */
    public function checkFieldDisable()
    {
        if ($this->canDisplayUseDefault() && $this->usedDefault()) {
            $this->getElement()->setDisabled(true);
        }
        return $this;
    }

    /**
     * Retrieve label of attribute scope
     *
     * GLOBAL | WEBSITE | STORE
     *
     * @return string
     */
    public function getScopeLabel()
    {
        $html = '';
        $attribute = $this->getElement()->getEntityAttribute();
        if (!$attribute || $this->_storeManager->isSingleStoreMode() || $attribute->getFrontendInput() == 'gallery') {
            return $html;
        }
        if ($attribute->isScopeGlobal()) {
            $html .= __('[GLOBAL]');
        } elseif ($attribute->isScopeWebsite()) {
            $html .= __('[WEBSITE]');
        } elseif ($attribute->isScopeStore()) {
            $html .= __('[STORE VIEW]');
        }

        return $html;
    }

    /**
     * Retrieve element label html
     *
     * @return string
     */
    public function getElementLabelHtml()
    {
        $element = $this->getElement();
        $label = $element->getLabel();
        if (!empty($label)) {
            $element->setLabel(__($label));
        }
        return $element->getLabelHtml();
    }

    /**
     * Retrieve element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        return $this->getElement()->getElementHtml();
    }

    /**
     * Default sore ID getter
     *
     * @return integer
     */
    protected function _getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
}
