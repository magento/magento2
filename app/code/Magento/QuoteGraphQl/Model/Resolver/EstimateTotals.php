<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\Data\TotalsInformationInterfaceFactory;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote\AddressFactory;

/**
 * Apply address and shipping method to totals estimate and return the quote
 */
class EstimateTotals implements ResolverInterface
{
    /**
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param AddressFactory $addressFactory
     * @param TotalsInformationManagementInterface $totalsInformationManagement
     * @param TotalsInformationInterfaceFactory $totalsInformationFactory
     */
    public function __construct(
        private readonly MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly AddressFactory $addressFactory,
        private readonly TotalsInformationManagementInterface $totalsInformationManagement,
        private readonly TotalsInformationInterfaceFactory $totalsInformationFactory
    ) {
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($args['input']['cart_id']);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlInputException(
                __(
                    'Could not find a cart with ID "%masked_id"',
                    [
                        'masked_id' => $args['input']['cart_id']
                    ]
                )
            );
        }

        if (empty($args['input']['address']['country_code'])) {
            throw new GraphQlInputException(__('Required parameter "country_code" is missing'));
        }

        $this->totalsInformationManagement->calculate($cartId, $this->getTotalsInformation($args['input']));

        return [
            'cart' => [
                'model' => $this->cartRepository->get($cartId)
            ]
        ];
    }

    /**
     * Retrieve an instance of totals information based on input data
     *
     * @param array $input
     * @return TotalsInformationInterface
     */
    private function getTotalsInformation(array $input): TotalsInformationInterface
    {
        $data = [TotalsInformationInterface::ADDRESS => $this->getAddress($input['address'])];

        $shippingMethod = $input['shipping_method'] ?? [];

        if (isset($shippingMethod['carrier_code']) && isset($shippingMethod['method_code'])) {
            $data[TotalsInformationInterface::SHIPPING_CARRIER_CODE] = $shippingMethod['carrier_code'];
            $data[TotalsInformationInterface::SHIPPING_METHOD_CODE] = $shippingMethod['method_code'];
        }

        return $this->totalsInformationFactory->create(['data' => $data]);
    }

    /**
     * Retrieve an instance of address based on address data
     *
     * @param array $data
     * @return AddressInterface
     */
    private function getAddress(array $data): AddressInterface
    {
        $data = [
            AddressInterface::KEY_COUNTRY_ID => $data['country_code'],
            AddressInterface::KEY_REGION => $data['region'][AddressInterface::KEY_REGION] ?? null,
            AddressInterface::KEY_REGION_ID => $data['region'][AddressInterface::KEY_REGION_ID] ?? null,
            AddressInterface::KEY_REGION_CODE => $data['region'][AddressInterface::KEY_REGION_CODE] ?? null,
            AddressInterface::KEY_POSTCODE => $data[AddressInterface::KEY_POSTCODE] ?? null,
        ];

        return $this->addressFactory->create(['data' => array_filter($data)]);
    }
}
