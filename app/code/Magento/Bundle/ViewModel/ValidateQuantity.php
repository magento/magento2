<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\ViewModel;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Block\Product\View as ProductView;

/**
 * ViewModel for Bundle Option Block
 */
class ValidateQuantity implements ArgumentInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ProductView
     */
    private $productView;

    /**
     * @param Json $serializer
     * @param ProductView $productView
     */
    public function __construct(
        Json $serializer,
        ProductView $productView
    ) {
        $this->serializer = $serializer;
        $this->productView = $productView;
    }

    public function getQuantityValidators(): string
    {
        return $this->serializer->serialize(
            $this->productView->getQuantityValidators()
        );
    }
}
