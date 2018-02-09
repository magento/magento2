<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestModuleGraphQlQuery\Model\Resolver;

use Magento\Framework\GraphQl\Query\PostFetchProcessorInterface;
use Magento\GraphQl\Model\ResolverInterface;
use Magento\TestModuleGraphQlQuery\Api\Data\ItemInterface;
use Magento\TestModuleGraphQlQuery\Model\Entity\ItemFactory;

class Item implements ResolverInterface
{
    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var PostFetchProcessorInterface[]
     */
    private $postFetchProcessors;

    /**
     * @param ItemFactory $itemFactory
     * @param PostFetchProcessorInterface[] $postFetchProcessors
     */
    public function __construct(ItemFactory $itemFactory, array $postFetchProcessors = [])
    {
        $this->itemFactory = $itemFactory;
        $this->postFetchProcessors = $postFetchProcessors;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(array $args, \Magento\GraphQl\Model\ResolverContextInterface $context)
    {
        $id = 0;
        /** @var \Magento\Framework\GraphQl\ArgumentInterface $arg */
        foreach ($args as $arg) {
            if ($arg->getName() === "id") {
                $id = (int)$arg->getValue();
            }
        }

        /** @var ItemInterface $item */
        $item = $this->itemFactory->create();
        $item->setItemId($id);
        $item->setName("itemName");
        $itemData = [
            'item_id' => $item->getItemId(),
            'name' => $item->getName()
        ];

        foreach ($this->postFetchProcessors as $postFetchProcessor) {
            $itemData = $postFetchProcessor->process($itemData);
        }

        return $itemData;
    }
}
