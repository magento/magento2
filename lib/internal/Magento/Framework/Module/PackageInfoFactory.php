<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * Factory to create PackageInfo class
 * @since 2.0.0
 */
class PackageInfoFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @return \Magento\Framework\Module\PackageInfo
     * @since 2.0.0
     */
    public function create()
    {
        $fullModuleList = $this->objectManager->create(\Magento\Framework\Module\FullModuleList::class);
        $reader = $this->objectManager->create(
            \Magento\Framework\Module\Dir\Reader::class,
            ['moduleList' => $fullModuleList]
        );
        return $this->objectManager->create(\Magento\Framework\Module\PackageInfo::class, ['reader' => $reader]);
    }
}
