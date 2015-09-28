<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\ObjectManagerInterface;

/**
 * Magento configuration View factory
 */
class ViewFactory
{
    const CLASS_NAME = 'Magento\Framework\Config\View';

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     *
     * Create View object
     *
     * @param array $arguments
     * @return \Magento\Framework\Config\View
     */
    public function createView(array $arguments)
    {
        return $this->objectManager->create(self::CLASS_NAME, ["configFiles" => $arguments]);
    }
}
