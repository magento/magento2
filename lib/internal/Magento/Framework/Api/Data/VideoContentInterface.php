<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Data;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Video Content data interface
 *
 * @api
 */
interface VideoContentInterface extends ExtensibleDataInterface
{
    const BASE64_ENCODED_DATA = 'base64_encoded_data';
    const TYPE = 'type';

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
     * Get provider YouTube|Vimeo
     *
     * @return string
     */
    public function getProvider();

    /**
     * Set provider
     *
     * @param string $data
     * @return $this
     */
    public function setProvider($data);

    /**
     * Get video URL
     *
     * @return string
     */
    public function getUrl();

    /**
     * Set video URL
     *
     * @param string $data
     * @return $this
     */
    public function setUrl($data);

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set Title
     *
     * @param string $data
     * @return $this
     */
    public function setTitle($data);

    /**
     * Get video Description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set video Description
     *
     * @param string $data
     * @return $this
     */
    public function setDescription($data);

    /**
     * Get Metadata
     *
     * @return string
     */
    public function getMetadata();

    /**
     * Set Metadata
     *
     * @param string $data
     * @return $this
     */
    public function setMetadata($data);
}
