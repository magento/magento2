<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Video Content data interface
 *
 * @api
 * @since 2.0.0
 */
interface VideoContentInterface extends ExtensibleDataInterface
{
    const TYPE = 'media_type';
    const PROVIDER = 'video_provider';
    const URL = 'video_url';
    const TITLE = 'video_title';
    const DESCRIPTION = 'video_description';
    const METADATA = 'video_metadata';

    /**
     * Retrieve MIME type
     *
     * @return string
     * @since 2.0.0
     */
    public function getMediaType();

    /**
     * Set MIME type
     *
     * @param string $mimeType
     * @return $this
     * @since 2.0.0
     */
    public function setMediaType($mimeType);

    /**
     * Get provider
     *
     * @return string
     * @since 2.0.0
     */
    public function getVideoProvider();

    /**
     * Set provider
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setVideoProvider($data);

    /**
     * Get video URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getVideoUrl();

    /**
     * Set video URL
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setVideoUrl($data);

    /**
     * Get Title
     *
     * @return string
     * @since 2.0.0
     */
    public function getVideoTitle();

    /**
     * Set Title
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setVideoTitle($data);

    /**
     * Get video Description
     *
     * @return string
     * @since 2.0.0
     */
    public function getVideoDescription();

    /**
     * Set video Description
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setVideoDescription($data);

    /**
     * Get Metadata
     *
     * @return string
     * @since 2.0.0
     */
    public function getVideoMetadata();

    /**
     * Set Metadata
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setVideoMetadata($data);
}
