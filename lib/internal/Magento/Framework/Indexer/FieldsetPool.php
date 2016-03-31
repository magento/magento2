<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use Magento\Framework\ObjectManagerInterface;

class FieldsetPool
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get fieldset class instance
     *
     * @param string $fieldsetClass
     * @throws \InvalidArgumentException
     * @return FieldsetInterface
     */
    public function get($fieldsetClass)
    {
        $handler = $this->objectManager->get($fieldsetClass);
        if (!$handler instanceof FieldsetInterface) {
            throw new \InvalidArgumentException(
                $fieldsetClass . ' doesn\'t implement \Magento\Framework\Indexer\FieldsetInterface'
            );
        }
        return $handler;
    }
}
