<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Composite processor to handle bundle product relations.
 */
class ProductRelationsProcessorComposite implements ProductRelationsProcessorInterface
{
    /**
     * @var ProductRelationsProcessorInterface[]
     */
    private $processors;

    /**
     * @param ProductRelationsProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        foreach ($processors as $processor) {
            if (!$processor instanceof ProductRelationsProcessorInterface) {
                throw new \InvalidArgumentException(
                    __('Product relations processor must implement %1.', ProductRelationsProcessorInterface::class)
                );
            }
        }

        $this->processors = $processors;
    }

    /**
     * @inheritDoc
     */
    public function process(
        ProductInterface $product,
        array $existingProductOptions,
        array $expectedProductOptions
    ): void {
        foreach ($this->processors as $processor) {
            $processor->process($product, $existingProductOptions, $expectedProductOptions);
        }
    }
}
