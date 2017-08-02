<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use Magento\Framework\ObjectManagerInterface;

/**
 * @api Retrieve Fieldset when implementing custom Indexer\Action
 * @since 2.0.0
 */
class FieldsetPool
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
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
