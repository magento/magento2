<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel\Address\Attribute\Source;

use Magento\Config\Model\Config\Source\Nooptreq as NooptreqSource;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\Options as CustomerOptions;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer prefix/suffix with website specified attribute source
 */
class PrefixSuffixWithWebsites
{
    /**
     * @var CustomerOptions
     */
    private $customerOptions;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Share
     */
    private $shareConfig;

    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $isRequired;

    /**
     * PrefixSuffixWithWebsites constructor.
     * @param CustomerOptions $customerOptions
     * @param StoreManagerInterface $storeManager
     * @param Share $shareConfig
     * @param AddressHelper $addressHelper
     */
    public function __construct(
        CustomerOptions $customerOptions,
        StoreManagerInterface $storeManager,
        Share $shareConfig,
        AddressHelper $addressHelper
    ) {
        $this->customerOptions = $customerOptions;
        $this->storeManager = $storeManager;
        $this->shareConfig = $shareConfig;
        $this->addressHelper = $addressHelper;
    }

    /**
     * Returns all options for prefix/suffix with websites mapping
     *
     * @param string $attributeCode
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAllOptions($attributeCode): array
    {
        if (!isset($this->options[$attributeCode])) {
            $this->options[$attributeCode] = [];
            $storeId = Store::DEFAULT_STORE_ID;
            $options = [];
            $websiteIds = [];

            if (!$this->shareConfig->isGlobalScope()) {
                foreach ($this->storeManager->getWebsites() as $website) {
                    $storeGroup = $this->storeManager->getGroup($website->getDefaultGroupId());
                    $storeId = $storeGroup->getDefaultStoreId();
                    $websiteOptions = $attributeCode === AddressInterface::PREFIX ?
                        $this->customerOptions->getNamePrefixOptions($storeId) :
                        $this->customerOptions->getNameSuffixOptions($storeId);

                    if ($websiteOptions) {
                        foreach ($websiteOptions as $value => $label) {
                            $websiteIds[trim($value)][] = $website->getId();
                        }

                        $options[] = $websiteOptions;
                    }
                }

                if (count($options) > 0) {
                    $options = array_merge(...$options);
                }
            } else {
                $websiteId = $this->storeManager->getDefaultStoreView()->getWebsiteId();
                $defaultOptions = $attributeCode === AddressInterface::PREFIX ?
                    $this->customerOptions->getNamePrefixOptions($storeId) :
                    $this->customerOptions->getNameSuffixOptions($storeId);

                if ($defaultOptions) {
                    foreach ($defaultOptions as $value => $label) {
                        $websiteIds[trim($value)][] = $websiteId;
                    }

                    $options = $defaultOptions;
                }
            }

            $this->options[$attributeCode] = $this->mapPrefixSuffixOptions($options);

            foreach ($this->options[$attributeCode] as &$option) {
                if (isset($websiteIds[$option['value']])) {
                    $option['website_ids'] = $websiteIds[$option['value']];
                }
            }
        }

        return $this->options[$attributeCode];
    }

    /**
     * Returns information if prefix/suffix is required per website
     *
     * @param string $attributeCode
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getIsRequired($attributeCode): array
    {
        if (!isset($this->isRequired[$attributeCode])) {
            $this->isRequired[$attributeCode] = [];
            $storeId = Store::DEFAULT_STORE_ID;
            $isRequired = [];

            if (!$this->shareConfig->isGlobalScope()) {
                foreach ($this->storeManager->getWebsites() as $website) {
                    $storeGroup = $this->storeManager->getGroup($website->getDefaultGroupId());
                    $storeId = $storeGroup->getDefaultStoreId();
                    $isRequired[$website->getId()] = $this->addressHelper->getConfig(
                        $attributeCode . '_show',
                        $storeId
                    ) === NooptreqSource::VALUE_REQUIRED;
                }
            } else {
                $websiteId = $this->storeManager->getDefaultStoreView()->getWebsiteId();
                $isRequired[$websiteId] = $this->addressHelper->getConfig(
                    $attributeCode . '_show',
                    $storeId
                ) === NooptreqSource::VALUE_REQUIRED;
            }

            $this->isRequired[$attributeCode] = $isRequired;
        }

        return $this->isRequired[$attributeCode];
    }

    /**
     * Map options array to valid source for UI select
     *
     * @param array $options
     *
     * @return array
     */
    private function mapPrefixSuffixOptions(array $options): array
    {
        $result = [];

        foreach ($options as $value => $label) {
            $result[] = ['label' => $label, 'value' => trim($value)];
        }

        return $result;
    }
}
