<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestModuleGraphQlQueryExtension\Model\Resolver;

use Magento\Framework\GraphQl\Query\PostFetchProcessorInterface;
use Magento\TestModuleGraphQlQuery\Model\Entity\ItemFactory;
use Magento\TestModuleGraphQlQuery\Model\Entity\Item;

/**
 * Class ItemPostProcessor
 */
class ItemPostProcessor implements PostFetchProcessorInterface
{
    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @param ItemFactory $itemFactory
     */
    public function __construct(ItemFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
    }

    /**
     * @inheritDoc
     */
    public function process(array $resultData)
    {
        /** @var Item $item */
        $item = $this->itemFactory->create();
        $itemId = $resultData['item_id'];
        $item->setItemId($itemId);
        $extensionAttributes = $item->getExtensionAttributes();
        $extensionAttributes->setIntegerList([$itemId + 1, $itemId + 2, $itemId + 3]);
        $resultData['integer_list'] = $extensionAttributes->getIntegerList();

        return $resultData;
    }
}
