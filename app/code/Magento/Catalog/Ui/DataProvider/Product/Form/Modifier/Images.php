<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        unset($meta[self::CODE_IMAGE_MANAGEMENT_GROUP]);

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
