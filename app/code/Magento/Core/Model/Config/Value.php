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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Config;

/**
 * Config data model
 *
 * @method \Magento\Core\Model\Resource\Config\Data _getResource()
 * @method \Magento\Core\Model\Resource\Config\Data getResource()
 * @method string getScope()
 * @method \Magento\Core\Model\Config\Value setScope(string $value)
 * @method int getScopeId()
 * @method \Magento\Core\Model\Config\Value setScopeId(int $value)
 * @method string getPath()
 * @method \Magento\Core\Model\Config\Value setPath(string $value)
 * @method string getValue()
 * @method \Magento\Core\Model\Config\Value setValue(string $value)
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Value extends \Magento\Core\Model\AbstractModel
{
    const ENTITY = 'core_config_data';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'core_config_data';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'config_data';

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_config;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\ConfigInterface $config
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\ConfigInterface $config,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Magento model constructor
     */
    protected function _construct()
    {
        $this->_init('Magento\Core\Model\Resource\Config\Data');
    }

    /**
     * Add availability call after load as public
     */
    public function afterLoad()
    {
        $this->_afterLoad();
    }

    /**
     * Check if config data value was changed
     *
     * @return bool
     */
    public function isValueChanged()
    {
        return $this->getValue() != $this->getOldValue();
    }

    /**
     * Get old value from existing config
     *
     * @return string
     */
    public function getOldValue()
    {
        $storeCode   = $this->getStoreCode();
        $websiteCode = $this->getWebsiteCode();
        $path        = $this->getPath();

        if ($storeCode) {
            return $this->_storeManager->getStore($storeCode)->getConfig($path);
        }
        if ($websiteCode) {
            return $this->_storeManager->getWebsite($websiteCode)->getConfig($path);
        }
        return (string) $this->_config->getValue($path, 'default');
    }


    /**
     * Get value by key for new user data from <section>/groups/<group>/fields/<field>
     *
     * @param string $key
     * @return string
     */
    public function getFieldsetDataValue($key)
    {
        $data = $this->_getData('fieldset_data');
        return (is_array($data) && isset($data[$key])) ? $data[$key] : null;
    }
}
