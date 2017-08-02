<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\Autocomplete;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Search\Model\Autocomplete\ItemFactory
 *
 * @since 2.0.0
 */
class ItemFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $data
     * @return Item
     * @since 2.0.0
     */
    public function create(array $data)
    {
        return $this->objectManager->create(\Magento\Search\Model\Autocomplete\Item::class, ['data' => $data]);
    }
}
