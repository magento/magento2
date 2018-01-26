<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundle\Plugin\InventoryApi;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;

/**
 * Class provides around Plugin on Magento\InventoryApi\Api\SourceItemsSaveInterface::execute
 * to prevent create source items for bundle products
 */
class PreventSourceItemsSavePlugin
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param SourceItemsSaveInterface $subject
     * @param callable $proceed
     * @param \Magento\InventoryApi\Api\Data\SourceItemInterface[] $sourceItems
     * @return void
     * @throws InputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(SourceItemsSaveInterface $subject, callable $proceed, array $sourceItems)
    {
        if (empty($sourceItems)) {
            throw new InputException(__('Input data is empty'));
        }
        foreach ($sourceItems as $key => $sourceItem) {
            $product = $this->productRepository->get($sourceItem->getSku());
            if ($product->getTypeId() == Type::TYPE_BUNDLE) {
                unset($sourceItems[$key]);
            }
        }
        if ($sourceItems) {
            $proceed($sourceItems);
        }
    }
}
