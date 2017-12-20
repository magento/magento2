<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

use Magento\Framework\ObjectManagerInterface;

/**
 * This class holds history about element modifications
 */
class ElementHistoryFactory
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
