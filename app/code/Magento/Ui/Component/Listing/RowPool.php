<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class RowPool
 */
class RowPool
{
    /**
     * @var RowInterface[]
     */
    protected $classPool;

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
     * @return RowInterface
     * @throws \InvalidArgumentException
     */
    public function get($class, array $arguments = [])
    {
        if (!isset($this->classPool[$class])) {
            $this->classPool[$class] = $this->objectManager->create($class, $arguments);
            if (!($this->classPool[$class] instanceof RowInterface)) {
                throw new \InvalidArgumentException(
                    sprintf('"%s" must implement the interface \Magento\Ui\Component\Listing\RowInterface', $class)
                );
            }
        }

        return $this->classPool[$class];
    }
}
