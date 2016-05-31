<?php
/**
 * Adminhtml block for fieldset of configurable product
 *
 * Copyright © 2016 Magento. All rights reserved.
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
<<<<<<< HEAD
use Magento\Framework\App\ObjectManager;
=======
>>>>>>> develop

class Bulk extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /** @var Image */
    protected $image;

    /**
     * @var ProductFactory
<<<<<<< HEAD
     */
    private $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\VariationMediaAttributes
=======
>>>>>>> develop
     */
    private $productFactory;

    /**
     * @var Config
     */
    private $catalogProductMediaConfig;

    /**
     * @param Context $context
     * @param Image $image
<<<<<<< HEAD
     * @param \Magento\ConfigurableProduct\Model\Product\VariationMediaAttributes $variationMediaAttributes
=======
     * @param Config $catalogProductMediaConfig
     * @param ProductFactory $productFactory
>>>>>>> develop
     */
    public function __construct(
        Context $context,
        Image $image,
<<<<<<< HEAD
        \Magento\ConfigurableProduct\Model\Product\VariationMediaAttributes $variationMediaAttributes
=======
        Config $catalogProductMediaConfig,
        ProductFactory $productFactory
>>>>>>> develop
    ) {
        parent::__construct($context);
        $this->image = $image;
        $this->productFactory = $productFactory;
        $this->catalogProductMediaConfig = $catalogProductMediaConfig;
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
<<<<<<< HEAD
        foreach ($this->getCatalogProductMediaConfig()->getMediaAttributeCodes() as $attributeCode) {
=======
        foreach ($this->catalogProductMediaConfig->getMediaAttributeCodes() as $attributeCode) {
>>>>>>> develop
            /* @var $attribute Attribute */
            $imageTypes[$attributeCode] = [
                'code' => $attributeCode,
                'value' => '',
                'label' => $attributeCode,
                'scope' => '',
                'name' => $attributeCode,
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
<<<<<<< HEAD
            $simple = $this->getProductFactory()->create()->setTypeId(Type::TYPE_SIMPLE)->getMediaAttributes();
=======
            $simple = $this->productFactory->create()->setTypeId(Type::TYPE_SIMPLE)->getMediaAttributes();
>>>>>>> develop
        }
        return $simple;
    }
}
