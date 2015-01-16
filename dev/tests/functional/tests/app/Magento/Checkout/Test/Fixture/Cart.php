<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class Cart
 * Fixture for cart
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Cart extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Checkout\Test\Repository\Cart';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Checkout\Test\Handler\Cart\CartInterface';

    protected $defaultDataSet = [];

    protected $entity_id = [
        'attribute_code' => 'entity_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $store_id = [
        'attribute_code' => 'store_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $created_at = [
        'attribute_code' => 'created_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => 'CURRENT_TIMESTAMP',
        'input' => '',
    ];

    protected $updated_at = [
        'attribute_code' => 'updated_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => '0000-00-00 00:00:00',
        'input' => '',
    ];

    protected $converted_at = [
        'attribute_code' => 'converted_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $is_active = [
        'attribute_code' => 'is_active',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '1',
        'input' => '',
    ];

    protected $is_virtual = [
        'attribute_code' => 'is_virtual',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $is_multi_shipping = [
        'attribute_code' => 'is_multi_shipping',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $items = [
        'attribute_code' => 'items',
        'backend_type' => 'virtual',
        'source' => 'Magento\Checkout\Test\Fixture\Cart\Items',
    ];

    protected $items_count = [
        'attribute_code' => 'items_count',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $items_qty = [
        'attribute_code' => 'items_qty',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '0.0000',
        'input' => '',
    ];

    protected $orig_order_id = [
        'attribute_code' => 'orig_order_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $store_to_base_rate = [
        'attribute_code' => 'store_to_base_rate',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '0.0000',
        'input' => '',
    ];

    protected $store_to_quote_rate = [
        'attribute_code' => 'store_to_quote_rate',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '0.0000',
        'input' => '',
    ];

    protected $base_currency_code = [
        'attribute_code' => 'base_currency_code',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $store_currency_code = [
        'attribute_code' => 'store_currency_code',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $quote_currency_code = [
        'attribute_code' => 'quote_currency_code',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $grand_total = [
        'attribute_code' => 'grand_total',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '0.0000',
        'input' => '',
    ];

    protected $base_grand_total = [
        'attribute_code' => 'base_grand_total',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '0.0000',
        'input' => '',
    ];

    protected $checkout_method = [
        'attribute_code' => 'checkout_method',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_id = [
        'attribute_code' => 'customer_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $customer_tax_class_id = [
        'attribute_code' => 'customer_tax_class_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $customer_group_id = [
        'attribute_code' => 'customer_group_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $customer_email = [
        'attribute_code' => 'customer_email',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_prefix = [
        'attribute_code' => 'customer_prefix',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_firstname = [
        'attribute_code' => 'customer_firstname',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_middlename = [
        'attribute_code' => 'customer_middlename',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_lastname = [
        'attribute_code' => 'customer_lastname',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_suffix = [
        'attribute_code' => 'customer_suffix',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_dob = [
        'attribute_code' => 'customer_dob',
        'backend_type' => 'datetime',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_note = [
        'attribute_code' => 'customer_note',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_note_notify = [
        'attribute_code' => 'customer_note_notify',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '1',
        'input' => '',
    ];

    protected $customer_is_guest = [
        'attribute_code' => 'customer_is_guest',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $remote_ip = [
        'attribute_code' => 'remote_ip',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $applied_rule_ids = [
        'attribute_code' => 'applied_rule_ids',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $reserved_order_id = [
        'attribute_code' => 'reserved_order_id',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $password_hash = [
        'attribute_code' => 'password_hash',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $coupon_code = [
        'attribute_code' => 'coupon_code',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $global_currency_code = [
        'attribute_code' => 'global_currency_code',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $base_to_global_rate = [
        'attribute_code' => 'base_to_global_rate',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $base_to_quote_rate = [
        'attribute_code' => 'base_to_quote_rate',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_taxvat = [
        'attribute_code' => 'customer_taxvat',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_gender = [
        'attribute_code' => 'customer_gender',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $subtotal = [
        'attribute_code' => 'subtotal',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $base_subtotal = [
        'attribute_code' => 'base_subtotal',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $subtotal_with_discount = [
        'attribute_code' => 'subtotal_with_discount',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $base_subtotal_with_discount = [
        'attribute_code' => 'base_subtotal_with_discount',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $is_changed = [
        'attribute_code' => 'is_changed',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $trigger_recollect = [
        'attribute_code' => 'trigger_recollect',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $ext_shipping_info = [
        'attribute_code' => 'ext_shipping_info',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $is_persistent = [
        'attribute_code' => 'is_persistent',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $gift_message_id = [
        'attribute_code' => 'gift_message_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $checkout_data = [
        'attribute_code' => 'checkout_data',
        'backend_type' => 'virtual',
        'group' => '',
        'source' => 'Magento\Checkout\Test\Fixture\Cart\CheckoutData',
    ];

    public function getEntityId()
    {
        return $this->getData('entity_id');
    }

    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    public function getConvertedAt()
    {
        return $this->getData('converted_at');
    }

    public function getIsActive()
    {
        return $this->getData('is_active');
    }

    public function getIsVirtual()
    {
        return $this->getData('is_virtual');
    }

    public function getIsMultiShipping()
    {
        return $this->getData('is_multi_shipping');
    }

    public function getItems()
    {
        return $this->getData('items');
    }

    public function getItemsCount()
    {
        return $this->getData('items_count');
    }

    public function getItemsQty()
    {
        return $this->getData('items_qty');
    }

    public function getOrigOrderId()
    {
        return $this->getData('orig_order_id');
    }

    public function getStoreToBaseRate()
    {
        return $this->getData('store_to_base_rate');
    }

    public function getStoreToQuoteRate()
    {
        return $this->getData('store_to_quote_rate');
    }

    public function getBaseCurrencyCode()
    {
        return $this->getData('base_currency_code');
    }

    public function getStoreCurrencyCode()
    {
        return $this->getData('store_currency_code');
    }

    public function getQuoteCurrencyCode()
    {
        return $this->getData('quote_currency_code');
    }

    public function getGrandTotal()
    {
        return $this->getData('grand_total');
    }

    public function getBaseGrandTotal()
    {
        return $this->getData('base_grand_total');
    }

    public function getCheckoutMethod()
    {
        return $this->getData('checkout_method');
    }

    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    public function getCustomerTaxClassId()
    {
        return $this->getData('customer_tax_class_id');
    }

    public function getCustomerGroupId()
    {
        return $this->getData('customer_group_id');
    }

    public function getCustomerEmail()
    {
        return $this->getData('customer_email');
    }

    public function getCustomerPrefix()
    {
        return $this->getData('customer_prefix');
    }

    public function getCustomerFirstname()
    {
        return $this->getData('customer_firstname');
    }

    public function getCustomerMiddlename()
    {
        return $this->getData('customer_middlename');
    }

    public function getCustomerLastname()
    {
        return $this->getData('customer_lastname');
    }

    public function getCustomerSuffix()
    {
        return $this->getData('customer_suffix');
    }

    public function getCustomerDob()
    {
        return $this->getData('customer_dob');
    }

    public function getCustomerNote()
    {
        return $this->getData('customer_note');
    }

    public function getCustomerNoteNotify()
    {
        return $this->getData('customer_note_notify');
    }

    public function getCustomerIsGuest()
    {
        return $this->getData('customer_is_guest');
    }

    public function getRemoteIp()
    {
        return $this->getData('remote_ip');
    }

    public function getAppliedRuleIds()
    {
        return $this->getData('applied_rule_ids');
    }

    public function getReservedOrderId()
    {
        return $this->getData('reserved_order_id');
    }

    public function getPasswordHash()
    {
        return $this->getData('password_hash');
    }

    public function getCouponCode()
    {
        return $this->getData('coupon_code');
    }

    public function getGlobalCurrencyCode()
    {
        return $this->getData('global_currency_code');
    }

    public function getBaseToGlobalRate()
    {
        return $this->getData('base_to_global_rate');
    }

    public function getBaseToQuoteRate()
    {
        return $this->getData('base_to_quote_rate');
    }

    public function getCustomerTaxvat()
    {
        return $this->getData('customer_taxvat');
    }

    public function getCustomerGender()
    {
        return $this->getData('customer_gender');
    }

    public function getSubtotal()
    {
        return $this->getData('subtotal');
    }

    public function getBaseSubtotal()
    {
        return $this->getData('base_subtotal');
    }

    public function getSubtotalWithDiscount()
    {
        return $this->getData('subtotal_with_discount');
    }

    public function getBaseSubtotalWithDiscount()
    {
        return $this->getData('base_subtotal_with_discount');
    }

    public function getIsChanged()
    {
        return $this->getData('is_changed');
    }

    public function getTriggerRecollect()
    {
        return $this->getData('trigger_recollect');
    }

    public function getExtShippingInfo()
    {
        return $this->getData('ext_shipping_info');
    }

    public function getIsPersistent()
    {
        return $this->getData('is_persistent');
    }

    public function getGiftMessageId()
    {
        return $this->getData('gift_message_id');
    }

    public function getCheckoutData()
    {
        return $this->getData('checkout_data');
    }
}
