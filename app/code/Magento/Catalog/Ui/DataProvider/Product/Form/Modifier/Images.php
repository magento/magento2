<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Gallery\DefaultValueProcessor;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;

/**
 * Class Images customizes Images panel
 *
 * @api
 * @since 101.0.0
 */
class Images extends AbstractModifier
{
    /**#@+
     * Attribute names
     */
    public const CODE_IMAGE_MANAGEMENT_GROUP = 'image-management';
    public const CODE_MEDIA_GALLERY = 'media_gallery';
    public const CODE_IMAGE = 'image';
    public const CODE_SMALL_IMAGE = 'small_image';
    public const CODE_THUMBNAIL = 'thumbnail';
    public const CODE_SWATCH_IMAGE = 'swatch_image';
    /**#@-*/

    /**
     * @var LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * @var DefaultValueProcessor
     */
    private $defaultValueProcessor;
    
    /**
     * @var ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @param LocatorInterface $locator
     * @param DefaultValueProcessor|null $defaultValueProcessor
     * @param ScopeOverriddenValue|null $scopeOverriddenValue
     */
    public function __construct(
        LocatorInterface $locator,
        ?DefaultValueProcessor $defaultValueProcessor = null,
        ?ScopeOverriddenValue $scopeOverriddenValue = null
    ) {
        $this->locator = $locator;
        $this->defaultValueProcessor = $defaultValueProcessor
            ?? ObjectManager::getInstance()->get(DefaultValueProcessor::class);
        $this->scopeOverriddenValue = $scopeOverriddenValue
            ?? ObjectManager::getInstance()->get(ScopeOverriddenValue::class);
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        unset($meta[self::CODE_IMAGE_MANAGEMENT_GROUP]);

        return $meta;
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        /** @var ProductInterface $product */
        $product = $this->locator->getProduct();
        $modelId = $product->getId();
        if (isset($data[$modelId][self::DATA_SOURCE_DEFAULT]['media_gallery'])
            && !empty($data[$modelId][self::DATA_SOURCE_DEFAULT]['media_gallery'])
            && !empty($data[$modelId][self::DATA_SOURCE_DEFAULT]['media_gallery']['images'])
        ) {
            foreach ($data[$modelId][self::DATA_SOURCE_DEFAULT]['media_gallery']['images'] as $index => $image) {
                if (!isset($image['label'])) {
                    $data[$modelId][self::DATA_SOURCE_DEFAULT]['media_gallery']['images'][$index]['label'] = "";
                }
            }
            $storeId = (int)$this->locator->getStore()->getId();
            if ($storeId !== Store::DEFAULT_STORE_ID) {
                $data[$modelId][self::DATA_SOURCE_DEFAULT]['media_gallery'] =
                    $this->defaultValueProcessor->process(
                        $product,
                        $data[$modelId][self::DATA_SOURCE_DEFAULT]['media_gallery'],
                        (int)$this->locator->getStore()->getId()
                    );
                foreach ($product->getMediaAttributes() as $attribute) {
                    if (!$attribute->isScopeGlobal()) {
                        $code = $attribute->getAttributeCode();
                        $data[$modelId]['use_default'][$code] = (int) !$this->scopeOverriddenValue->containsValue(
                            ProductInterface::class,
                            $product,
                            $code,
                            $storeId
                        );
                    }
                }
            }
        }

        return $data;
    }
}
