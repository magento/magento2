<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

/**
 * Adminhtml block for fieldset of configurable product
 *
 * @api
 * @since 2.0.0
 */
class Bulk extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * @var \Magento\Catalog\Helper\Image
     * @since 2.0.0
     */
    protected $image;

    /**
     * @var ProductFactory
     * @since 2.1.0
     */
    private $productFactory;

    /**
     * @var Config
     * @since 2.1.0
     */
    private $catalogProductMediaConfig;

    /**
     * @param Context $context
     * @param Image $image
     * @param Config $catalogProductMediaConfig
     * @param ProductFactory $productFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Image $image,
        Config $catalogProductMediaConfig,
        ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->image = $image;
        $this->productFactory = $productFactory;
        $this->catalogProductMediaConfig = $catalogProductMediaConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCaption()
    {
        return __('Bulk Images &amp; Price');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getNoImageUrl()
    {
        return $this->image->getDefaultPlaceholderUrl('thumbnail');
    }

    /**
     * Get image types data
     *
     * @return array
     * @since 2.0.0
     */
    public function getImageTypes()
    {
        $imageTypes = [];
        foreach ($this->catalogProductMediaConfig->getMediaAttributeCodes() as $attributeCode) {
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
     * @since 2.0.0
     */
    public function getMediaAttributes()
    {
        static $simple;
        if (empty($simple)) {
            $simple = $this->productFactory->create()->setTypeId(Type::TYPE_SIMPLE)->getMediaAttributes();
        }
        return $simple;
    }
}
