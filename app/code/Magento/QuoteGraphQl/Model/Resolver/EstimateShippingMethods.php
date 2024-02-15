<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
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
        return $this->getAvailableShippingMethodsForAddress($args, $cart);
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

        if (empty($args['input'][AddressInterface::KEY_COUNTRY_ID])) {
            throw new GraphQlInputException(
                __(
                    'Required parameter "%country_id" is missing',
                    [
                        'country_id' => AddressInterface::KEY_COUNTRY_ID
                    ]
                )
            );
        }

        if (isset($args['input']['address']) && empty($args['input']['address'])) {
            throw new GraphQlInputException(__('Parameter(s) for address are missing'));
        }

        if (isset($args['input']['address']['region']) && empty($args['input']['address']['region'])) {
            throw new GraphQlInputException(__('Parameter(s) for region are missing'));
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
        /** @var $address AddressInterface */
        $address = $this->addressFactory->create();
        $shippingMethods = [];

        $address->addData([
            AddressInterface::KEY_COUNTRY_ID => $args['input'][AddressInterface::KEY_COUNTRY_ID]
        ]);
        if (!empty($args['input']['address'])) {
            $data = $args['input']['address'];
            if (!empty($data['region'])) {
                $address->addData([
                    AddressInterface::KEY_REGION => $data['region'][AddressInterface::KEY_REGION] ?? '',
                    AddressInterface::KEY_REGION_ID => $data['region'][AddressInterface::KEY_REGION_ID] ?? '',
                    AddressInterface::KEY_REGION_CODE => $data['region'][AddressInterface::KEY_REGION_CODE] ?? ''
                ]);
            }
            $address->addData([
                AddressInterface::KEY_FIRSTNAME => $data[AddressInterface::KEY_FIRSTNAME] ?? '',
                AddressInterface::KEY_LASTNAME => $data[AddressInterface::KEY_LASTNAME] ?? '',
                AddressInterface::KEY_MIDDLENAME => $data[AddressInterface::KEY_MIDDLENAME] ?? '',
                AddressInterface::KEY_PREFIX => $data[AddressInterface::KEY_PREFIX] ?? '',
                AddressInterface::KEY_SUFFIX => $data[AddressInterface::KEY_SUFFIX] ?? '',
                AddressInterface::KEY_VAT_ID => $data[AddressInterface::KEY_VAT_ID] ?? '',
                AddressInterface::KEY_COMPANY => $data[AddressInterface::KEY_COMPANY] ?? '',
                AddressInterface::KEY_TELEPHONE => $data[AddressInterface::KEY_TELEPHONE] ?? '',
                AddressInterface::KEY_CITY => $data[AddressInterface::KEY_CITY] ?? '',
                AddressInterface::KEY_STREET => $data[AddressInterface::KEY_STREET] ?? '',
                AddressInterface::KEY_POSTCODE => $data[AddressInterface::KEY_POSTCODE] ?? '',
                AddressInterface::KEY_FAX => $data[AddressInterface::KEY_FAX] ?? '',
                AddressInterface::CUSTOM_ATTRIBUTES => $data[AddressInterface::CUSTOM_ATTRIBUTES] ?? ''
            ]);
        }
        foreach ($this->shipmentEstimation->estimateByExtendedAddress($cart->getId(), $address) as $method) {
            $shippingMethods[] = $this->formatMoneyTypeData->execute(
                $this->dataObjectConverter->toFlatArray($method, [], ShippingMethodInterface::class),
                $cart->getCurrency()->getQuoteCurrencyCode()
            );
        }
        return $shippingMethods;
    }
}
