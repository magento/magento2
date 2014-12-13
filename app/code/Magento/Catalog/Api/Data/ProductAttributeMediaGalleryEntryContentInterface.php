<?php
/**
 * Product Media Content
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Api\Data;

interface ProductAttributeMediaGalleryEntryContentInterface
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
     * Retrieve MIME type
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Retrieve image name
     *
     * @return string
     */
    public function getName();
}
