<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Address;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\Create\Form\Address as AddressBlock;
use Magento\Directory\Model\AllowedCountries;
use Magento\Store\Model\ScopeInterface;

class StoreAddressCollection extends \Magento\Customer\Model\ResourceModel\Address\Collection
{
    /**
     * @var AddressBlock
     */
    private $addressBlock;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addressBlock = ObjectManager::getInstance()->get(AddressBlock::class);
        $this->allowedCountryReader = ObjectManager::getInstance()->get(AllowedCountries::class);
    }

    /**
     * Set customer filter
     *
     * @param \Magento\Customer\Model\Customer|array $customer
     * @return $this
     */
    public function setCustomerFilter($customer)
    {
        parent::setCustomerFilter($customer);

        $storeId = $this->addressBlock->getStoreId() ?? null;
        if ($storeId) {
            $allowedCountries = $this->allowedCountryReader->getAllowedCountries(
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $this->addAttributeToFilter('country_id', ['in' => $allowedCountries]);
        }
        return $this;
    }
}
