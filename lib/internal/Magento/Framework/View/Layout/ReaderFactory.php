<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

/**
 * Class ReaderFactory
 */
class ReaderFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create reader instance with specified parameters
     *
     * @param string $className
     * @param array $data
     * @return \Magento\Framework\View\Layout\ReaderInterface
     * @throws \InvalidArgumentException
     */
    public function create($className, array $data = [])
    {
        $reader = $this->objectManager->create($className, $data);
        if (!$reader instanceof \Magento\Framework\View\Layout\ReaderInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement \Magento\Framework\View\Layout\ReaderInterface'
            );
        }
        return $reader;
    }
}
