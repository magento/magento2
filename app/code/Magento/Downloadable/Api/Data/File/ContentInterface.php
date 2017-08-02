<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data\File;

/**
 * @codeCoverageIgnore
 * @api
 * @since 2.0.0
 */
interface ContentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Retrieve data (base64 encoded content)
     *
     * @return string
     * @since 2.0.0
     */
    public function getFileData();

    /**
     * Set data (base64 encoded content)
     *
     * @param string $fileData
     * @return $this
     * @since 2.0.0
     */
    public function setFileData($fileData);

    /**
     * Retrieve file name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set file name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Downloadable\Api\Data\File\ContentExtensionInterface $extensionAttributes
    );
}
