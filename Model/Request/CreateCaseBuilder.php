<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Request;

/**
 * Class CreateCaseBuilder
 */
class CreateCaseBuilder implements CreateCaseBuilderInterface
{
    /**
     * @var PurchaseBuilder
     */
    private $purchaseBuilder;

    /**
     * @param PurchaseBuilder $purchaseBuilder
     */
    public function __construct(
        PurchaseBuilder $purchaseBuilder
    ) {
        $this->purchaseBuilder = $purchaseBuilder;
    }

    /**
     * @inheritdoc
     */
    public function build($orderId)
    {
        return $this->purchaseBuilder;
    }
}
