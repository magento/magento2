<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Catalog product gallery attribute
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery
 *
 * @since 2.0.0
 */
class Gallery extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Gallery field name suffix
     *
     * @var string
     * @since 2.1.0
     */
    protected $fieldNameSuffix = 'product';

    /**
     * Gallery html id
     *
     * @var string
     * @since 2.1.0
     */
    protected $htmlId = 'media_gallery';

    /**
     * Gallery name
     *
     * @var string
     * @since 2.1.0
     */
    protected $name = 'product[media_gallery]';

    /**
     * Html id for data scope
     *
     * @var string
     * @since 2.1.0
     */
    protected $image = 'image';

    /**
     * @var string
     * @since 2.1.0
     */
    protected $formName = 'product_form';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.1.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Data\Form
     * @since 2.1.0
     */
    protected $form;

    /**
     * @var Registry
     * @since 2.1.0
     */
    protected $registry;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param \Magento\Framework\Data\Form $form
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Registry $registry,
        \Magento\Framework\Data\Form $form,
        $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->form = $form;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getElementHtml()
    {
        $html = $this->getContentHtml();
        return $html;
    }

    /**
     * Get product images
     *
     * @return array|null
     * @since 2.1.0
     */
    public function getImages()
    {
        return $this->registry->registry('current_product')->getData('media_gallery') ?: null;
    }

    /**
     * Prepares content block
     *
     * @return string
     * @since 2.0.0
     */
    public function getContentHtml()
    {
        /* @var $content \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content */
        $content = $this->getChildBlock('content');
        $content->setId($this->getHtmlId() . '_content')->setElement($this);
        $content->setFormName($this->formName);
        $galleryJs = $content->getJsObjectName();
        $content->getUploader()->getConfig()->setMegiaGallery($galleryJs);
        return $content->toHtml();
    }

    /**
     * @return string
     * @since 2.1.0
     */
    protected function getHtmlId()
    {
        return $this->htmlId;
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getFieldNameSuffix()
    {
        return $this->fieldNameSuffix;
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getDataScopeHtmlId()
    {
        return $this->image;
    }

    /**
     * Check "Use default" checkbox display availability
     *
     * @param Attribute $attribute
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getScopeLabel($attribute)
    {
        $html = '';
        if ($this->storeManager->isSingleStoreMode()) {
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
     * Retrieve data object related with form
     *
     * @return ProductInterface|Product
     * @since 2.0.0
     */
    public function getDataObject()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Retrieve attribute field name
     *
     *
     * @param Attribute $attribute
     * @return string
     * @since 2.0.0
     */
    public function getAttributeFieldName($attribute)
    {
        $name = $attribute->getAttributeCode();
        if ($suffix = $this->getFieldNameSuffix()) {
            $name = $this->form->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function toHtml()
    {
        return $this->getElementHtml();
    }

    /**
     * Default sore ID getter
     *
     * @return integer
     * @since 2.0.0
     */
    protected function _getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
}
