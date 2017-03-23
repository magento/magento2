<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Data;

/**
 * Image Content data interface
 *
 * @api
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
     */
    public function getBase64EncodedData();

    /**
     * Set media data (base64 encoded content)
     *
     * @param string $data
     * @return $this
     */
    public function setBase64EncodedData($data);

    /**
     * Retrieve MIME type
     *
     * @return string
     */
    public function getType();

    /**
     * Set MIME type
     *
     * @param string $mimeType
     * @return $this
     */
    public function setType($mimeType);

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
}
