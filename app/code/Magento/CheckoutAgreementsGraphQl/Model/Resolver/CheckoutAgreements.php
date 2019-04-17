<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreementsGraphQl\Model\Resolver;

use Magento\CheckoutAgreementsGraphQl\Model\Resolver\DataProvider\CheckoutAgreements as CheckoutAgreementsDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Checkout Agreements resolver, used for GraphQL request processing
 */
class CheckoutAgreements implements ResolverInterface
{
    /**
     * @var CheckoutAgreementsDataProvider
     */
    private $checkoutAgreementsDataProvider;

    /**
     * @param CheckoutAgreementsDataProvider $checkoutAgreementsDataProvider
     */
    public function __construct(
        CheckoutAgreementsDataProvider $checkoutAgreementsDataProvider
    ) {
        $this->checkoutAgreementsDataProvider = $checkoutAgreementsDataProvider;
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
        $checkoutAgreementsData = $this->checkoutAgreementsDataProvider->getData();

        return $checkoutAgreementsData;
    }
}
