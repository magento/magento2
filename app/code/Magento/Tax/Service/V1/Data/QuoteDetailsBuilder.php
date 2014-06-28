<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Service\V1\Data;

/**
 * QuoteDetailsBuilder
 *
 * @method QuoteDetails create()
 */
class QuoteDetailsBuilder extends \Magento\Framework\Service\Data\AbstractObjectBuilder
{
    /**
     * QuoteDetails item builder
     *
     * @var \Magento\Tax\Service\V1\Data\QuoteDetails\ItemBuilder
     */
    protected $itemBuilder;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param \Magento\Tax\Service\V1\Data\QuoteDetails\ItemBuilder $itemBuilder
     * @param \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        \Magento\Tax\Service\V1\Data\QuoteDetails\ItemBuilder $itemBuilder,
        \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder
    ) {
        parent::__construct($objectFactory);
        $this->itemBuilder = $itemBuilder;
        $this->addressBuilder = $addressBuilder;
    }

    /**
     * Set customer billing address
     *
     * @param \Magento\Customer\Service\V1\Data\Address $address
     * @return $this
     */
    public function setBillingAddress($address)
    {
        return $this->_set(QuoteDetails::KEY_BILLING_ADDRESS, $address);
    }

    /**
     * Set customer shipping address
     *
     * @param \Magento\Customer\Service\V1\Data\Address $address
     * @return $this
     */
    public function setShippingAddress($address)
    {
        return $this->_set(QuoteDetails::KEY_SHIPPING_ADDRESS, $address);
    }

    /**
     * Set customer tax class id
     *
     * @param int $taxClassId
     * @return $this
     */
    public function setCustomerTaxClassId($taxClassId)
    {
        return $this->_set(QuoteDetails::KEY_CUSTOMER_TAX_CLASS_ID, $taxClassId);
    }

    /**
     * Set quote items
     *
     * @param \Magento\Tax\Service\V1\Data\QuoteDetails\Item[]|null $items
     * @return $this
     */
    public function setItems($items)
    {
        return $this->_set(QuoteDetails::KEY_ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     */
    protected function _setDataValues(array $data)
    {
        if (array_key_exists(QuoteDetails::KEY_BILLING_ADDRESS, $data)) {
            $data[QuoteDetails::KEY_BILLING_ADDRESS] = $this->addressBuilder->populateWithArray(
                $data[QuoteDetails::KEY_BILLING_ADDRESS]
            )->create();
        }
        if (array_key_exists(QuoteDetails::KEY_SHIPPING_ADDRESS, $data)) {
            $data[QuoteDetails::KEY_SHIPPING_ADDRESS] = $this->addressBuilder->populateWithArray(
                $data[QuoteDetails::KEY_SHIPPING_ADDRESS]
            )->create();
        }
        if (array_key_exists(QuoteDetails::KEY_ITEMS, $data)) {
            $items = [];
            foreach ($data[QuoteDetails::KEY_ITEMS] as $itemArray) {
                $items[] = $this->itemBuilder->populateWithArray($itemArray)->create();
            }
            $data[QuoteDetails::KEY_ITEMS] = $items;
        }
        return parent::_setDataValues($data);
    }
}
