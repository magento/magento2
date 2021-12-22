<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps;

use Magento\Backend\Helper\Js;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Adminhtml block for fieldset of configurable product
 *
 * @api
 * @since 100.0.2
 */
class Bulk extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $image;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Config
     */
    private $catalogProductMediaConfig;

    /**
     * @param Context $context
     * @param Image $image
     * @param Config $catalogProductMediaConfig
     * @param ProductFactory $productFactory
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     */
    public function __construct(
        Context $context,
        Image $image,
        Config $catalogProductMediaConfig,
        ProductFactory $productFactory,
        array $data = [],
        JsonHelper $jsonHelper = null
    ) {
        $data['jsonHelper'] = $jsonHelper ?? ObjectManager::getInstance()->get(JsonHelper::class);
        parent::__construct($context, $data);
        $this->image = $image;
        $this->productFactory = $productFactory;
        $this->catalogProductMediaConfig = $catalogProductMediaConfig;
    }

    /**
     * @inheritdoc
     */
    public function getCaption()
    {
        return __('Bulk Images &amp; Price');
    }

    /**
     * Return no image url.
     *
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
     * Return media attributes.
     *
     * @return array
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
