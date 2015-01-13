<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class OptionsFactory
 */
class OptionsFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Getting provider object
     *
     * @param string $class
     * @param array $arguments
     * @return OptionsInterface
     * @throws \InvalidArgumentException
     */
    public function create($class, array $arguments = [])
    {
        $object = $this->objectManager->create($class, $arguments);
        if (!($object instanceof OptionsInterface)) {
            throw new \InvalidArgumentException(
                sprintf('"%s" must implement the interface \Magento\Ui\Component\Listing\OptionsInterface', $class)
            );
        }

        return $object;
    }
}
