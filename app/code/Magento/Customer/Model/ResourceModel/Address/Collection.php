<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Address;

use Magento\Customer\Model\Config\Share;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\ObjectManager;

/**
 * Customers collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Collection extends \Magento\Eav\Model\Entity\Collection\VersionControl\AbstractCollection
{
    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $shareConfig;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct(
        ?Share $share = null,
        ?AllowedCountries $allowedCountryReader = null
    )
    {
        $this->_init(\Magento\Customer\Model\Address::class, \Magento\Customer\Model\ResourceModel\Address::class);
        $this->shareConfig = $share ?: ObjectManager::getInstance()
            ->get(Share::class);
        $this->allowedCountryReader = $allowedCountryReader ?: ObjectManager::getInstance()
            ->get(AllowedCountries::class);
    }

    /**
     * Set customer filter
     *
     * @param \Magento\Customer\Model\Customer|array $customer
     * @return $this
     */
    public function setCustomerFilter($customer)
    {
        if (is_array($customer)) {
            $this->addAttributeToFilter('parent_id', ['in' => $customer]);
        } elseif ($customer->getId()) {
            $this->addAttributeToFilter('parent_id', $customer->getId());
        } else {
            $this->addAttributeToFilter('parent_id', '-1');
        }
        return $this;
    }

    /**
     * Set store filter
     *
     * @param \Magento\Store\Model\Store|integer $storeId
     * @return $this
     */
    public function setScopeFilter($storeId)
    {
        // Checks if a country present in the allowed countries list.
        $allowedCountries = $this->allowedCountryReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, $storeId);
        if ($this->shareConfig->isGlobalScope()) {
            $this->addAttributeToFilter('country_id', ['in' => $allowedCountries]);
        }
        return $this;
    }
}
