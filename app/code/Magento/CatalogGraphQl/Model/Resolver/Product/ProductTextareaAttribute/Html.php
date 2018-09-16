<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextareaAttribute;

use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Model\Product as ModelProduct;

class Html implements FormatInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var OutputHelper
     */
    private $outputHelper;

    /**
     * @param ValueFactory $valueFactory
     * @param OutputHelper $outputHelper
     */
    public function __construct(
        ValueFactory $valueFactory,
        OutputHelper $outputHelper
    ) {
        $this->valueFactory = $valueFactory;
        $this->outputHelper = $outputHelper;
    }

    /**
     * @inheritdoc
     */
    public function getContent(
        ModelProduct $product,
        string $fieldName
    ): string {
        return $this->outputHelper->productAttribute($product, $product->getData($fieldName), $fieldName);
    }
}