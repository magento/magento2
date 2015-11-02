<?php
/**
 * Adminhtml block for fieldset of configurable product
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps;

class Bulk extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /** @var \Magento\Catalog\Helper\Image */
    protected $image;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\VariationMediaAttributes
     */
    protected $variationMediaAttributes;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Image $image
     * @param \Magento\ConfigurableProduct\Model\Product\VariationMediaAttributes $variationMediaAttributes
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Image $image,
        \Magento\ConfigurableProduct\Model\Product\VariationMediaAttributes $variationMediaAttributes
    ) {
        parent::__construct($context);
        $this->image = $image;
        $this->variationMediaAttributes = $variationMediaAttributes;
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
        foreach ($this->variationMediaAttributes->getMediaAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $imageTypes[$attribute->getAttributeCode()] = [
                'code' => $attribute->getAttributeCode(),
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
        return $this->variationMediaAttributes->getMediaAttributes();
    }
}
