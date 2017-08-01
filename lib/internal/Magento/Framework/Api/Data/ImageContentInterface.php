<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Data;

/**
 * Image Content data interface
 *
 * @api
 * @since 2.0.0
 */
interface ImageContentInterface
{
    const BASE64_ENCODED_DATA = 'base64_encoded_data';
    const TYPE = 'type';
    const NAME = 'name';

    /**
     * Retrieve media data (base64 encoded content)
     *
     * @return string
     * @since 2.0.0
     */
    public function getBase64EncodedData();

    /**
     * Set media data (base64 encoded content)
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setBase64EncodedData($data);

    /**
     * Retrieve MIME type
     *
     * @return string
     * @since 2.0.0
     */
    public function getType();

    /**
     * Set MIME type
     *
     * @param string $mimeType
     * @return $this
     * @since 2.0.0
     */
    public function setType($mimeType);

    /**
     * Retrieve image name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set image name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);
}
