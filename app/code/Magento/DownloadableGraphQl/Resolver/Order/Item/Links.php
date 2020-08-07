<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Resolver\Order\Item;

use Magento\Downloadable\Model\ResourceModel\Link\Collection;
use Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory;
use Magento\DownloadableGraphQl\Model\ConvertLinksToArray;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Resolver fetches downloadable order item links and formats it according to the GraphQL schema.
 */
class Links implements ResolverInterface
{
    /**
     * @var ConvertLinksToArray
     */
    private $convertLinksToArray;

    /**
     * @var CollectionFactory
     */
    private $linkCollectionFactory;

    /**
     * @param ConvertLinksToArray $convertLinksToArray
     * @param CollectionFactory $linkCollectionFactory
     */
    public function __construct(
        ConvertLinksToArray $convertLinksToArray,
        CollectionFactory $linkCollectionFactory
    ) {
        $this->convertLinksToArray = $convertLinksToArray;
        $this->linkCollectionFactory = $linkCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        /** @var OrderItem $orderItem */
        $orderItem = $value['model'];

        $orderLinks = $orderItem->getProductOptionByCode('links');

        /** @var Collection */
        $linksCollection = $this->linkCollectionFactory->create();
        $linksCollection->addTitleToResult($store->getStoreId())
            ->addPriceToResult($store->getWebsiteId())
            ->addFieldToFilter('main_table.link_id', ['in' => $orderLinks]);

        return $this->convertLinksToArray->execute($linksCollection->getItems());
    }
}
