<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Placeholder;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class PlaceholderFactory
{
    /**
     * @const string Environment type
     */
    const TYPE_ENVIRONMENT = 'environment';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $types;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $types
     */
    public function __construct(ObjectManagerInterface $objectManager, array $types = [])
    {
        $this->objectManager = $objectManager;
        $this->types = $types;
    }

    /**
     * Create placeholder
     *
     * @param string $type
     * @return PlaceholderInterface
     * @throws LocalizedException
     */
    public function create($type)
    {
        if (!isset($this->types[$type])) {
            throw new LocalizedException(__('There is no defined type ' . $type));
        }

        $object = $this->objectManager->create($this->types[$type]);

        if (!$object instanceof PlaceholderInterface) {
            throw new LocalizedException(__('Object is not instance of ' . PlaceholderInterface::class));
        }

        return $object;
    }
}
