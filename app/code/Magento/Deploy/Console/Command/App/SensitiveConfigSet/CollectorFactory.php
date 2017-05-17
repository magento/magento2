<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App\SensitiveConfigSet;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CollectorFactory creates instance of CollectorInterface
 */
class CollectorFactory
{
    /**
     * Constant for interactive collector type
     */
    const TYPE_INTERACTIVE = 'interactive';

    /**
     * Constant for simple collector type
     */
    const TYPE_SIMPLE = 'simple';

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $types = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $types List of collector class names this factory can create.
     *                     Every class should implement
     *                     \Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorInterface
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $types = []
    ) {
        $this->objectManager = $objectManager;
        $this->types = $types;
    }

    /**
     * Creates instance of CollectorInterface by given type
     *
     * The value of the $type associated with the name of the class of collector object to create
     * There are several types of collectors
     * @see \Magento\Deploy\Console\Command\App\SensitiveConfigSet\InteractiveCollector
     * @see \Magento\Deploy\Console\Command\App\SensitiveConfigSet\SimpleCollector
     *
     * @param string $type
     * @return CollectorInterface
     * @throws LocalizedException If collector type not exist in registered types array.
     */
    public function create($type)
    {
        if (!isset($this->types[$type])) {
            throw new LocalizedException(__('Class for type "%1" was not declared', $type));
        }

        $object = $this->objectManager->create($this->types[$type]);

        if (!$object instanceof CollectorInterface) {
            throw new LocalizedException(
                __('%1 does not implement %2', get_class($object), CollectorInterface::class)
            );
        }

        return $object;
    }
}
