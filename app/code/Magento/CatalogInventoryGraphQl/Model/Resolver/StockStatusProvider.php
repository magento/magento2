<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class StockStatusProvider implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var StockStatusRepositoryInterface
     */
    private $stockStatusRepository;

    /**
     * @param ValueFactory $valueFactory
     * @param StockStatusRepositoryInterface $stockStatusRepository
     */
    public function __construct(ValueFactory $valueFactory, StockStatusRepositoryInterface $stockStatusRepository)
    {
        $this->valueFactory = $valueFactory;
        $this->stockStatusRepository = $stockStatusRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): Value
    {
        if (!array_key_exists('model', $value) || !$value['model'] instanceof ProductInterface) {
            $result = function () {
                return null;
            };

            return $this->valueFactory->create($result);
        }

        /* @var $product ProductInterface */
        $product = $value['model'];

        $stockStatus = $this->stockStatusRepository->get($product->getId());

        $result = function () use ($stockStatus) {
            return $stockStatus->getStockStatus();
        };

        return $this->valueFactory->create($result);
    }

}
