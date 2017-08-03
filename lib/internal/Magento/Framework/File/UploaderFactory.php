<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\File;

/**
 * Class \Magento\Framework\File\UploaderFactory
 *
 * @since 2.0.0
 */
class UploaderFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    private $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new uploader instance
     *
     * @param array $data
     * @return Uploader
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(\Magento\Framework\File\Uploader::class, $data);
    }
}
