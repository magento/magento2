<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product gallery attribute
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Adminhtml gallery block
 */
class Gallery extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Gallery field name suffix
     *
     * @var string
     */
    protected $fieldNameSuffix = 'product';

    /**
     * Gallery html id
     *
     * @var string
     */
    protected $htmlId = 'media_gallery';

    /**
     * Gallery name
     *
     * @var string
     */
    protected $name = 'product[media_gallery]';

    /**
     * Html id for data scope
     *
     * @var string
     */
    protected $image = 'image';

    /**
     * @var string
     */
    protected $formName = 'product_form';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $form;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param \Magento\Framework\Data\Form $form
     * @param array $data
     * @param DataPersistorInterface|null $dataPersistor
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Registry $registry,
        \Magento\Framework\Data\Form $form,
        $data = [],
        DataPersistorInterface $dataPersistor = null
    ) {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->form = $form;
        $this->dataPersistor = $dataPersistor ?: ObjectManager::getInstance()->get(DataPersistorInterface::class);
        parent::__construct($context, $data);
    }

    /**
     * Returns element html.
     *
     * @return string
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
     */
    public function getImages()
    {
        $images = $this->getDataObject()->getData('media_gallery') ?: null;
        if ($images === null) {
            $images = ((array)$this->dataPersistor->get('catalog_product'))['product']['media_gallery'] ?? null;
        }

        return $images;
    }

    /**
     * Get value for given type.
     *
     * @param string $type
     * @return string|null
     */
    public function getImageValue(string $type)
    {
        $product = (array)$this->dataPersistor->get('catalog_product');
        return $product['product'][$type] ?? null;
    }

    /**
     * Prepares content block
     *
     * @return string
     */
    public function getContentHtml()
    {
        /* @var $content \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content */
        $content = $this->getChildBlock('content');
        $content->setId($this->getHtmlId() . '_content')->setElement($this);
        $content->setFormName($this->formName);
        $galleryJs = $content->getJsObjectName();
        $content->getUploader()->getConfig()->setMediaGallery($galleryJs);
        return $content->toHtml();
    }

    /**
     * Returns html id
     *
     * @return string
     */
    protected function getHtmlId()
    {
        return $this->htmlId;
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns suffix for field name
     *
     * @return string
     */
    public function getFieldNameSuffix()
    {
        return $this->fieldNameSuffix;
    }

    /**
     * Returns data scope html id
     *
     * @return string
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
     */
    public function getDataObject()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Retrieve attribute field name
     *
     * @param Attribute $attribute
     * @return string
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
     * Returns html content of the block
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->getElementHtml();
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
