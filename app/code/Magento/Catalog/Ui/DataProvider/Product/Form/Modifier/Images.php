<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;

/**
 * Class Images customizes Images panel
 */
class Images extends AbstractModifier
{
    /**#@+
     * Attribute names
     */
    const CODE_IMAGE_MANAGEMENT_GROUP = 'image-management';
    const CODE_MEDIA_GALLERY = 'media_gallery';
    const CODE_IMAGE = 'image';
    const CODE_SMALL_IMAGE = 'small_image';
    const CODE_THUMBNAIL = 'thumbnail';
    const CODE_SWATCH_IMAGE = 'swatch_image';
    /**#@-*/

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param LocatorInterface $locator
     */
    public function __construct(LocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $this->customizeImagesTab($meta);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $modelId = $this->locator->getProduct()->getId();

        if ($modelId != null) {
            if (isset($data[$modelId][self::DATA_SOURCE_DEFAULT][self::CODE_MEDIA_GALLERY])) {
                unset($data[$modelId][self::DATA_SOURCE_DEFAULT][self::CODE_MEDIA_GALLERY]);
            }
            if (isset($data[$modelId][self::DATA_SOURCE_DEFAULT][self::CODE_IMAGE])) {
                unset($data[$modelId][self::DATA_SOURCE_DEFAULT][self::CODE_IMAGE]);
            }
            if (isset($data[$modelId][self::DATA_SOURCE_DEFAULT][self::CODE_SMALL_IMAGE])) {
                unset($data[$modelId][self::DATA_SOURCE_DEFAULT][self::CODE_SMALL_IMAGE]);
            }
            if (isset($data[$modelId][self::DATA_SOURCE_DEFAULT][self::CODE_THUMBNAIL])) {
                unset($data[$modelId][self::DATA_SOURCE_DEFAULT][self::CODE_THUMBNAIL]);
            }
            if (isset($data[$modelId][self::DATA_SOURCE_DEFAULT][self::CODE_SWATCH_IMAGE])) {
                unset($data[$modelId][self::DATA_SOURCE_DEFAULT][self::CODE_SWATCH_IMAGE]);
            }
        }

        return $data;
    }

    /**
     * Remove Images tab from meta because it's block is rendered in layout
     *
     * @param array $meta
     * @return array
     */
    protected function customizeImagesTab(array $meta)
    {
        foreach (array_keys($meta) as $groupName) {
            if ($groupName === self::CODE_IMAGE_MANAGEMENT_GROUP) {
                unset($meta[self::CODE_IMAGE_MANAGEMENT_GROUP]);
            }
        }

        return $meta;
    }
}
