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
 * Catalog product gallery attribute
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Eav\Model\Entity\Attribute;

class Gallery extends AbstractElement
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\StoreManagerInterface $storeManager,
        $data = array()
    ) {
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getContentHtml();
        return $html;
    }

    /**
     * Prepares content block
     *
     * @return string
     */
    public function getContentHtml()
    {

        /* @var $content \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content */
        $content = $this->_layout->createBlock('Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content');
        $content->setId($this->getHtmlId() . '_content')->setElement($this);
        $galleryJs = $content->getJsObjectName();
        $content->getUploader()->getConfig()->setMegiaGallery($galleryJs);
        return $content->toHtml();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return '';
    }

    /**
     * Check "Use default" checkbox display availability
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function canDisplayUseDefault($attribute)
    {
        if (!$attribute->isScopeGlobal() && $this->getDataObject()->getStoreId()) {
            return true;
        }

        return false;
    }

    /**
     * Check default value usage fact
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function usedDefault($attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        $defaultValue = $this->getDataObject()->getAttributeDefaultValue($attributeCode);

        if (!$this->getDataObject()->getExistsStoreValueFlag($attributeCode)) {
            return true;
        } elseif ($this->getValue() == $defaultValue &&
            $this->getDataObject()->getStoreId() != $this->_getDefaultStoreId()
        ) {
            return false;
        }
        if ($defaultValue === false && !$attribute->getIsRequired() && $this->getValue()) {
            return false;
        }
        return $defaultValue === false;
    }

    /**
     * Retrieve label of attribute scope
     *
     * GLOBAL | WEBSITE | STORE
     *
     * @param Attribute $attribute
     * @return string
     */
    public function getScopeLabel($attribute)
    {
        $html = '';
        if ($this->_storeManager->isSingleStoreMode()) {
            return $html;
        }

        if ($attribute->isScopeGlobal()) {
            $html .= '<br/>' . __('[GLOBAL]');
        } elseif ($attribute->isScopeWebsite()) {
            $html .= '<br/>' . __('[WEBSITE]');
        } elseif ($attribute->isScopeStore()) {
            $html .= '<br/>' . __('[STORE VIEW]');
        }
        return $html;
    }

    /**
     * Retrieve data object related with form
     *
     *@return mixed
     */
    public function getDataObject()
    {
        return $this->getForm()->getDataObject();
    }

    /**
     * Retrieve attribute field name
     *
     *
     * @param Attribute $attribute
     * @return string
     */
    public function getAttributeFieldName($attribute)
    {
        $name = $attribute->getAttributeCode();
        if ($suffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    /**
     * Check readonly attribute
     *
     * @param Attribute|string $attribute
     * @return boolean
     */
    public function getAttributeReadonly($attribute)
    {
        if (is_object($attribute)) {
            $attribute = $attribute->getAttributeCode();
        }

        if ($this->getDataObject()->isLockedAttribute($attribute)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        return '<tr><td class="value" colspan="3">' . $this->getElementHtml() . '</td></tr>';
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
