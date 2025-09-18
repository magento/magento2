<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Cart\AssignShippingMethodToCart;
use Magento\QuoteGraphQl\Model\ErrorMapper;
use Magento\QuoteGraphQl\Model\TotalsBuilder;
use Psr\Log\LoggerInterface;

/**
 * Apply address and shipping method to totals estimate and return the quote
 */
class EstimateTotals implements ResolverInterface
{
    /**
     * EstimateTotals Constructor
     *
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param TotalsInformationManagementInterface $totalsInformationManagement
     * @param ErrorMapper $errorMapper
     * @param AssignShippingMethodToCart $assignShippingMethodToCart
     * @param LoggerInterface $logger
     * @param TotalsBuilder $totalsBuilder
     */
    public function __construct(
        private readonly MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly TotalsInformationManagementInterface $totalsInformationManagement,
        private readonly ErrorMapper $errorMapper,
        private readonly AssignShippingMethodToCart $assignShippingMethodToCart,
        private readonly LoggerInterface $logger,
        private readonly TotalsBuilder $totalsBuilder
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $input = $args['input'] ?? [];

        if (empty($input['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($input['cart_id']);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlInputException(
                __(
                    'Could not find a cart with ID "%masked_id"',
                    [
                        'masked_id' => $input['cart_id']
                    ]
                ),
                $exception,
                $this->errorMapper->getErrorMessageId('Could not find a cart with ID')
            );
        }

        $addressData = $input['address'] ?? [];
        if (empty($addressData['country_code'])) {
            throw new GraphQlInputException(__('Required parameter "country_code" is missing'));
        }

        $totalsInfo = $this->totalsBuilder->execute($addressData, $input['shipping_method'] ?? []);
        $this->totalsInformationManagement->calculate($cartId, $totalsInfo);
        $this->updateShippingMethod($totalsInfo, $cartId);

        return [
            'cart' => [
                'model' => $this->cartRepository->get($cartId)
            ]
        ];
    }

    /**
     * Update shipping method if provided
     *
     * @param TotalsInformationInterface $totalsInfo
     * @param int $cartId
     * @return void
     * @throws GraphQlInputException
     */
    private function updateShippingMethod(TotalsInformationInterface $totalsInfo, int $cartId): void
    {
        try {
            if ($totalsInfo->getShippingCarrierCode() && $totalsInfo->getShippingMethodCode()) {
                $this->assignShippingMethodToCart->execute(
                    $this->cartRepository->get($cartId),
                    $totalsInfo->getAddress(),
                    $totalsInfo->getShippingCarrierCode(),
                    $totalsInfo->getShippingMethodCode()
                );
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
