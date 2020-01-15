<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Catalog product gallery attribute
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Gallery extends AbstractBlock
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
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Form
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
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param Form $form
     * @param DataPersistorInterface $dataPersistor
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Registry $registry,
        Form $form,
        DataPersistorInterface $dataPersistor,
        $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->form = $form;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $data);
    }

    /**
     * Returns element html.
     *
     * @return string
     */
    public function getElementHtml()
    {
        return $this->getContentHtml();
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
        return (!$attribute->isScopeGlobal() && $this->getDataObject()->getStoreId());
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
        }

        if ($this->getValue() == $defaultValue &&
            $this->getDataObject()->getStoreId() != $this->_getDefaultStoreId()) {
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
        return Store::DEFAULT_STORE_ID;
    }
}
