<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * Factory to create PackageInfo class
 */
class PackageInfoFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @return \Magento\Framework\Module\PackageInfo
     */
    public function create()
    {
        $moduleList = $this->objectManager->create('Magento\Setup\Model\ModuleList');
        $reader = $this->objectManager->create('Magento\Framework\Module\Dir\Reader', ['moduleList' => $moduleList]);
        return $this->objectManager->create('Magento\Framework\Module\PackageInfo', ['reader' => $reader]);
    }
}