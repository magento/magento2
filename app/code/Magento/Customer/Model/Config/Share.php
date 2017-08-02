<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config;

/**
 * Customer sharing config model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
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
     * @var \Magento\Customer\Model\ResourceModel\Customer
     * @since 2.0.0
     */
    protected $_customerResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_customerResource = $customerResource;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Check whether current customers sharing scope is global
     *
     * @return bool
     * @since 2.0.0
     */
    public function isGlobalScope()
    {
        return !$this->isWebsiteScope();
    }

    /**
     * Check whether current customers sharing scope is website
     *
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [self::SHARE_GLOBAL => __('Global'), self::SHARE_WEBSITE => __('Per Website')];
    }

    /**
     * Check for email duplicates before saving customers sharing options
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value == self::SHARE_GLOBAL) {
            if ($this->_customerResource->findEmailDuplicates()) {
                //@codingStandardsIgnoreStart
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'We can\'t share customer accounts globally when the accounts share identical email addresses on more than one website.'
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
     * @since 2.0.0
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
