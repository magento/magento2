<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Image Data Interface
 */
interface ImageInterface extends ExtensibleDataInterface
{
    const BASE64_ENCODED_DATA = 'base64_encoded_data';
    const TYPE = 'type';
    const NAME = 'name';
    const ID = 'id';

    /**
     * Retrieve image ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set image ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Retrieve image data (base64 encoded content)
     *
     * @return string
     */
    public function getBase64EncodedData();

    /**
     * Set image data (base64 encoded content)
     *
     * @param string $data
     * @return $this
     */
    public function setBase64EncodedData($data);

    /**
     * Retrieve Image type
     *
     * @return string
     */
    public function getType();

    /**
     * Set Image type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

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
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Framework\Api\Data\ImageExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Framework\Api\Data\ImageExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Framework\Api\Data\ImageExtensionInterface $extensionAttributes);
}
