<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\QuoteGraphQl\Model\FormatMoneyTypeData;

/**
 * @inheritdoc
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EstimateShippingMethods implements ResolverInterface
{
    /**
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param AddressFactory $addressFactory
     * @param ShipmentEstimationInterface $shipmentEstimation
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param ShippingMethodConverter $shippingMethodConverter
     * @param FormatMoneyTypeData $formatMoneyTypeData
     */
    public function __construct(
        private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        private CartRepositoryInterface $cartRepository,
        private AddressFactory $addressFactory,
        private ShipmentEstimationInterface $shipmentEstimation,
        private ExtensibleDataObjectConverter $dataObjectConverter,
        private ShippingMethodConverter $shippingMethodConverter,
        private FormatMoneyTypeData $formatMoneyTypeData,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->validateInput($args);
        try {
            $cart = $this->cartRepository->get($this->maskedQuoteIdToQuoteId->execute($args['input']['cart_id']));
        } catch (NoSuchEntityException $ex) {
            throw new GraphQlInputException(
                __(
                    'Could not find a cart with ID "%masked_id"',
                    [
                        'masked_id' => $args['input']['cart_id']
                    ]
                )
            );
        }
        return $this->getAvailableShippingMethodsForAddress($args['input']['address'], $cart);
    }

    /**
     * Validates arguments passed to resolver
     *
     * @param array $args
     * @throws GraphQlInputException
     */
    private function validateInput(array $args)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        if (empty($args['input']['address']['country_code'])) {
            throw new GraphQlInputException(__('Required parameter "country_code" is missing'));
        }

        if (isset($args['input']['address']['region']) && empty($args['input']['address']['region'])) {
            throw new GraphQlInputException(__('Missing parameter(s) for region input'));
        }
    }

    /**
     * Get the list of available shipping methods given a cart, country_id and optional customer address parameters
     *
     * @param array $args
     * @param CartInterface $cart
     * @return array
     */
    private function getAvailableShippingMethodsForAddress(array $args, CartInterface $cart): array
    {
        $data = [
            AddressInterface::KEY_COUNTRY_ID => $args['country_code'],
            AddressInterface::KEY_REGION => $args['region'][AddressInterface::KEY_REGION] ?? null,
            AddressInterface::KEY_REGION_ID => $args['region'][AddressInterface::KEY_REGION_ID] ?? null,
            AddressInterface::KEY_REGION_CODE => $args['region'][AddressInterface::KEY_REGION_CODE] ?? null,
            AddressInterface::KEY_POSTCODE => $args[AddressInterface::KEY_POSTCODE] ?? null,
        ];

        /** @var $address AddressInterface */
        $address = $this->addressFactory->create(['data' => array_filter($data)]);
        $shippingMethods = [];

        foreach ($this->shipmentEstimation->estimateByExtendedAddress($cart->getId(), $address) as $method) {
            $shippingMethods[] = $this->formatMoneyTypeData->execute(
                $this->dataObjectConverter->toFlatArray($method, [], ShippingMethodInterface::class),
                $cart->getCurrency()->getQuoteCurrencyCode()
            );
        }
        return $shippingMethods;
    }
}
