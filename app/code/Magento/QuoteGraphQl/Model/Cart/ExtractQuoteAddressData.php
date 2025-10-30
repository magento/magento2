<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\EavGraphQl\Model\Output\Value\GetAttributeValueInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Item;

/**
 * Extract address fields from an Quote Address model
 */
class ExtractQuoteAddressData
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     */

    /**
     * @var Uid
     */
    private Uid $uidEncoder;

    /**
     * @var GetAttributeValueInterface
     */
    private GetAttributeValueInterface $getAttributeValue;

    /**
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param Uid $uidEncoder
     * @param GetAttributeValueInterface $getAttributeValue
     */
    public function __construct(
        ExtensibleDataObjectConverter $dataObjectConverter,
        Uid $uidEncoder,
        GetAttributeValueInterface $getAttributeValue
    ) {
        $this->dataObjectConverter = $dataObjectConverter;
        $this->uidEncoder = $uidEncoder;
        $this->getAttributeValue = $getAttributeValue;
    }

    /**
     * Converts Address model to flat array
     *
     * @param QuoteAddress $address
     * @return array
     */
    public function execute(QuoteAddress $address): array
    {
        $addressData = $this->dataObjectConverter->toFlatArray($address, [], AddressInterface::class);
        $addressData['model'] = $address;

        $addressData = array_merge(
            $addressData,
            [
                'country' => [
                    'code' => $address->getCountryId(),
                    'label' => $address->getCountry()
                ],
                'region' => [
                    'code' => $address->getRegionCode(),
                    'label' => $address->getRegion(),
                    'region_id'=> $address->getRegionId()
                ],
                'uid' => $this->uidEncoder->encode((string)$address->getAddressId()) ,
                'id' => $address->getCustomerAddressId(),
                'street' => $address->getStreet(),
                'items_weight' => $address->getWeight(),
                'customer_notes' => $address->getCustomerNotes(),
                'custom_attributes' => array_map(
                    function (AttributeInterface $attribute) {
                        return $this->getAttributeValue->execute(
                            'customer_address',
                            $attribute->getAttributeCode(),
                            $attribute->getValue()
                        );
                    },
                    $address->getCustomAttributes() ?? []
                )
            ]
        );

        if (!$address->hasItems()) {
            return $addressData;
        }

        foreach ($address->getAllItems() as $addressItem) {
            if ($addressItem instanceof Item) {
                $itemId = $addressItem->getItemId();
            } else {
                $itemId = $addressItem->getQuoteItemId();
            }
            $productData = $addressItem->getProduct()->getData();
            $productData['model'] = $addressItem->getProduct();
            $addressData['cart_items'][] = [
                'cart_item_id' => $itemId,
                'quantity' => $addressItem->getQty()
            ];
            $addressData['cart_items_v2'][] = [
                'id' => $itemId,
                'quantity' => $addressItem->getQty(),
                'product' => $productData,
                'model' => $addressItem,
            ];
        }
        return $addressData;
    }
}
