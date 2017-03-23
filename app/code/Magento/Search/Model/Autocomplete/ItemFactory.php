<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\Autocomplete;

use Magento\Framework\ObjectManagerInterface;

class ItemFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $data
     * @return Item
     */
    public function create(array $data)
    {
        return $this->objectManager->create(\Magento\Search\Model\Autocomplete\Item::class, ['data' => $data]);
    }
}
