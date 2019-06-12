<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema;

use Magento\Framework\ObjectManagerInterface;

/**
 * Element history container factory.
 */
class ElementHistoryFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create element history container.
     *
     * @param array $data
     *  - Should consist of 2 params:
     *      new
     *      old
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->objectManager->create(ElementHistory::class, $data);
    }
}
