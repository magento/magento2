<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App\SensitiveConfigSet;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CollectorFactory creates instance of CollectorInterface.
 */
class CollectorFactory
{
    /**#@+
     * Constant for collector types.
     */
    const TYPE_INTERACTIVE = 'interactive';
    const TYPE_SIMPLE = 'simple';
    /**#@-*/

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
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $types = []
    ) {
        $this->objectManager = $objectManager;
        $this->types = $types;
    }

    /**
     * Create instance of CollectorInterface by given type.
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
