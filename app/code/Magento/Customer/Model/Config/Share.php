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
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer sharing config model
 *
 * @category   Magento
 * @package    Magento_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\Config;

class Share extends \Magento\Core\Model\Config\Value
    implements \Magento\Core\Model\Option\ArrayInterface
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
    const SHARE_GLOBAL  = 0;
    const SHARE_WEBSITE = 1;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Customer\Model\Resource\Customer
     */
    protected $_customerResource;

    /**
     * Constructor
     *
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Customer\Model\Resource\Customer $customerResource
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Customer\Model\Resource\Customer $customerResource,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_customerResource = $customerResource;
        parent::__construct($context, $registry, $storeManager, $config, $resource, $resourceCollection, $data);
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
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_CUSTOMER_ACCOUNT_SHARE) == self::SHARE_WEBSITE;
    }

    /**
     * Get possible sharing configuration options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            self::SHARE_GLOBAL  => __('Global'),
            self::SHARE_WEBSITE => __('Per Website'),
        );
    }

    /**
     * Check for email dublicates before saving customers sharing options
     *
     * @return \Magento\Customer\Model\Config\Share
     * @throws \Magento\Core\Exception
     */
    public function _beforeSave()
    {
        $value = $this->getValue();
        if ($value == self::SHARE_GLOBAL) {
            if ($this->_customerResource->findEmailDuplicates()) {
                throw new \Magento\Core\Exception(
                    //@codingStandardsIgnoreStart
                    __('Cannot share customer accounts globally because some customer accounts with the same emails exist on multiple websites and cannot be merged.')
                    //@codingStandardsIgnoreEnd
                );
            }
        }
        return $this;
    }
}
