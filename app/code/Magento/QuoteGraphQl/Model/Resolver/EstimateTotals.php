<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Framework\App\ObjectManager;
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
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * Apply address and shipping method to totals estimate and return the quote
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EstimateTotals implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private GetCartForUser $getCartForUser;

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
     * @param GetCartForUser|null $getCartForUser
     */
    public function __construct(
        private readonly MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly TotalsInformationManagementInterface $totalsInformationManagement,
        private readonly ErrorMapper $errorMapper,
        private readonly AssignShippingMethodToCart $assignShippingMethodToCart,
        private readonly LoggerInterface $logger,
        private readonly TotalsBuilder $totalsBuilder,
        ?GetCartForUser $getCartForUser = null
    ) {
        $this->getCartForUser = $getCartForUser ?? ObjectManager::getInstance()->get(GetCartForUser::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $input = $args['input'] ?? [];
        $maskedCartId = $input['cart_id'];

        if (empty($maskedCartId)) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlInputException(
                __(
                    'Could not find a cart with ID "%masked_id"',
                    [
                        'masked_id' => $maskedCartId
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

        $currentUserId = $context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);

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
