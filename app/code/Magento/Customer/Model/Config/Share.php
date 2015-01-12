<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config;

/**
 * Customer sharing config model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Share extends \Magento\Framework\App\Config\Value implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Xml config path to customers sharing scope value
     *
     */
    const XML_PATH_CUSTOMER_ACCOUNT_SHARE = 'customer/account_share/scope';

    /**
     * Possible customer sharing scopes
     *
     */
    const SHARE_GLOBAL = 0;

    const SHARE_WEBSITE = 1;

    /**
     * @var \Magento\Customer\Model\Resource\Customer
     */
    protected $_customerResource;

    /** @var  \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Resource\Customer $customerResource
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Resource\Customer $customerResource,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_customerResource = $customerResource;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Check whether current customers sharing scope is global
     *
     * @return bool
     */
    public function isGlobalScope()
    {
        return !$this->isWebsiteScope();
    }

    /**
     * Check whether current customers sharing scope is website
     *
     * @return bool
     */
    public function isWebsiteScope()
    {
        return $this->_config->getValue(
            self::XML_PATH_CUSTOMER_ACCOUNT_SHARE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == self::SHARE_WEBSITE;
    }

    /**
     * Get possible sharing configuration options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [self::SHARE_GLOBAL => __('Global'), self::SHARE_WEBSITE => __('Per Website')];
    }

    /**
     * Check for email duplicates before saving customers sharing options
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value == self::SHARE_GLOBAL) {
            if ($this->_customerResource->findEmailDuplicates()) {
                //@codingStandardsIgnoreStart
                throw new \Magento\Framework\Model\Exception(
                    __(
                        'Cannot share customer accounts globally because some customer accounts with the same emails exist on multiple websites and cannot be merged.'
                    )
                );
                //@codingStandardsIgnoreEnd
            }
        }
        return $this;
    }

    /**
     * Returns shared website Ids.
     *
     * @param int $websiteId the ID to use if website scope is on
     * @return int[]
     */
    public function getSharedWebsiteIds($websiteId)
    {
        $ids = [];
        if ($this->isWebsiteScope()) {
            $ids[] = $websiteId;
        } else {
            foreach ($this->_storeManager->getWebsites() as $website) {
                $ids[] = $website->getId();
            }
        }
        return $ids;
    }
}
