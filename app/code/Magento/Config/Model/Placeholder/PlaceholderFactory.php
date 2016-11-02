<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Placeholder;

use Magento\Framework\ObjectManagerInterface;

class PlaceholderFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create placeholder
     *
     * @param string $type
     * @return PlaceholderInterface
     */
    public function create($type)
    {
        $object = $this->objectManager->create($type);

        if (!$object instanceof PlaceholderInterface) {
            throw new \LogicException('Object is not instance of ' . PlaceholderInterface::class);
        }

        return $object;
    }
}
