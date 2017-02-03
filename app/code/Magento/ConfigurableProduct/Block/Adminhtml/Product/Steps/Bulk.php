<?php
/**
 * Adminhtml block for fieldset of configurable product
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\ObjectManager;

class Bulk extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /** @var Image */
    protected $image;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\VariationMediaAttributes
     */
    protected $variationMediaAttributes;

    /**
     * @var Config
     */
    private $catalogProductMediaConfig;

    /**
     * @param Context $context
     * @param Image $image
     * @param \Magento\ConfigurableProduct\Model\Product\VariationMediaAttributes $variationMediaAttributes
     */
    public function __construct(
        Context $context,
        Image $image,
        \Magento\ConfigurableProduct\Model\Product\VariationMediaAttributes $variationMediaAttributes
    ) {
        parent::__construct($context);
        $this->image = $image;
        $this->variationMediaAttributes = $variationMediaAttributes;
    }

    /**
     * The getter function to get the productFactory for real application code
     *
     * @deprecated
     *
     * @return ProductFactory
     */
    private function getProductFactory()
    {
        if ($this->productFactory === null) {
            $this->productFactory = ObjectManager::getInstance()->get('Magento\Catalog\Model\ProductFactory');
        }
        return $this->productFactory;
    }

    /**
     * The setter function to inject the mocked productFactory during unit test
     *
     * @deprecated
     *
     * @throws \LogicException
     *
     * @param ProductFactory $productFactory
     *
     * @return void
     */
    public function setProductFactory(ProductFactory $productFactory)
    {
        if ($this->productFactory != null) {
            throw new \LogicException(__('productFactory is already set'));
        }
        $this->productFactory = $productFactory;
    }

    /**
     * The getter function to get the catalogProductMediaConfig for real application code
     *
     * @deprecated
     *
     * @return Config
     */
    private function getCatalogProductMediaConfig()
    {
        if ($this->catalogProductMediaConfig === null) {
            $this->catalogProductMediaConfig = ObjectManager::getInstance()
                ->get('Magento\Catalog\Model\Product\Media\Config');
        }
        return $this->catalogProductMediaConfig;
    }

    /**
     * The setter function to inject the mocked catalogProductMediaConfig during unit test
     *
     * @deprecated
     *
     * @throws \LogicException
     *
     * @param Config $catalogProductMediaConfig
     *
     * @return void
     */
    public function setCatalogProductMediaConfig(Config $catalogProductMediaConfig)
    {
        if ($this->catalogProductMediaConfig != null) {
            throw new \LogicException(__('catalogProductMediaConfig is already set'));
        }
        $this->catalogProductMediaConfig = $catalogProductMediaConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Bulk Images &amp; Price');
    }

    /**
     * @return string
     */
    public function getNoImageUrl()
    {
        return $this->image->getDefaultPlaceholderUrl('thumbnail');
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getImageTypes()
    {
        $imageTypes = [];
        foreach ($this->getCatalogProductMediaConfig()->getMediaAttributeCodes() as $attributeCode) {
            /* @var $attribute Attribute */
            $imageTypes[$attributeCode] = [
                'code' => $attributeCode,
                'value' => '',
                'name' => '',
            ];
        }
        return $imageTypes;
    }

    /**
     * @return array
     */
    public function getMediaAttributes()
    {
        static $simple;
        if (empty($simple)) {
            $simple = $this->getProductFactory()->create()->setTypeId(Type::TYPE_SIMPLE)->getMediaAttributes();
        }
        return $simple;
    }
}
