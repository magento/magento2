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
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
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
     * Serializer
     *
     * @var Json
     */
    private $serializer;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param ConvertLinksToArray $convertLinksToArray
     * @param CollectionFactory $linkCollectionFactory
     * @param ValueFactory $valueFactory
     * @param Json $serializer
     */
    public function __construct(
        ConvertLinksToArray $convertLinksToArray,
        CollectionFactory $linkCollectionFactory,
        ValueFactory $valueFactory,
        Json $serializer
    ) {
        $this->convertLinksToArray = $convertLinksToArray;
        $this->linkCollectionFactory = $linkCollectionFactory;
        $this->valueFactory = $valueFactory;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        return $this->valueFactory->create(function () use ($value, $store) {
            if (!isset($value['model'])) {
                throw new LocalizedException(__('"model" value should be specified'));
            }

            if ($value['model'] instanceof OrderItemInterface) {
                return $this->formatLinksData($value['model'], $store);
            } elseif ($value['model'] instanceof InvoiceItemInterface
                || $value['model'] instanceof CreditmemoItemInterface
                || $value['model'] instanceof ShipmentItemInterface) {
                $item = $value['model'];
                return $this->formatLinksData($item->getOrderItem(), $store);
            }
            return null;
        });
    }

    /**
     * Format values from order links item
     *
     * @param OrderItemInterface $item
     * @param StoreInterface $store
     * @return array
     */
    private function formatLinksData(
        OrderItemInterface $item,
        StoreInterface $store
    ): array {
        $linksData = [];
        if ($item->getProductType() === 'downloadable') {
            $orderLinks = $item->getProductOptionByCode('links') ?? [];

            /** @var Collection */
            $linksCollection = $this->linkCollectionFactory->create();
            $linksCollection->addTitleToResult($store->getId())
                ->addPriceToResult($store->getWebsiteId())
                ->addFieldToFilter('main_table.link_id', ['in' => $orderLinks]);

            $linksData = $this->convertLinksToArray->execute($linksCollection->getItems());
        }
        return $linksData;
    }
}
