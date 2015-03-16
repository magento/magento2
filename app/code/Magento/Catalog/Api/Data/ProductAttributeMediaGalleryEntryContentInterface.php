<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Product Media Content
 */
interface ProductAttributeMediaGalleryEntryContentInterface extends ExtensibleDataInterface
{
    const DATA = 'entry_data';
    const MIME_TYPE = 'mime_type';
    const NAME = 'name';

    /**
     * Retrieve media data (base64 encoded content)
     *
     * @return string
     */
    public function getEntryData();

    /**
     * Set media data (base64 encoded content)
     *
     * @param string $entryData
     * @return $this
     */
    public function setEntryData($entryData);

    /**
     * Retrieve MIME type
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Set MIME type
     *
     * @param string $mimeType
     * @return $this
     */
    public function setMimeType($mimeType);

    /**
     * Retrieve image name
     *
     * @return string
     */
    public function getName();

    /**
     * Set image name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentExtensionInterface $extensionAttributes
    );
}
