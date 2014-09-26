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
namespace Magento\Customer\Model;

/**
 * Customer group model
 *
 * @method \Magento\Customer\Model\Resource\Group _getResource()
 * @method \Magento\Customer\Model\Resource\Group getResource()
 * @method string getCustomerGroupCode()
 * @method \Magento\Customer\Model\Group setCustomerGroupCode(string $value)
 * @method \Magento\Customer\Model\Group setTaxClassId(int $value)
 */
class Group extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Xml config path for create account default group
     */
    const XML_PATH_DEFAULT_ID = 'customer/create_account/default_group';

    const NOT_LOGGED_IN_ID = 0;

    const CUST_GROUP_ALL = 32000;

    const ENTITY = 'customer_group';

    const GROUP_CODE_MAX_LENGTH = 32;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'customer_group';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'object';

    /**
     * @var array
     */
    protected static $_taxClassIds = array();

    /**
     * @var \Magento\Store\Model\StoresConfig
     */
    protected $_storesConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoresConfig $storesConfig
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoresConfig $storesConfig,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_storesConfig = $storesConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Customer\Model\Resource\Group');
    }

    /**
     * Alias for setCustomerGroupCode
     *
     * @param string $value
     * @return $this
     */
    public function setCode($value)
    {
        return $this->setCustomerGroupCode($value);
    }

    /**
     * Alias for getCustomerGroupCode
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getCustomerGroupCode();
    }

    /**
     * Get the tax class id for the specified group or this group if the groupId is null
     *
     * @param int|null $groupId The id of the group whose tax class id is being sought
     * @return int
     */
    public function getTaxClassId($groupId = null)
    {
        if (!is_null($groupId)) {
            if (empty(self::$_taxClassIds[$groupId])) {
                $this->load($groupId);
                self::$_taxClassIds[$groupId] = $this->getData('tax_class_id');
            }
            $this->setData('tax_class_id', self::$_taxClassIds[$groupId]);
        }
        return $this->getData('tax_class_id');
    }

    /**
     * Determine if this group is used as the create account default group
     *
     * @return bool
     */
    public function usesAsDefault()
    {
        $data = $this->_storesConfig->getStoresConfigByPath(self::XML_PATH_DEFAULT_ID);
        if (in_array($this->getId(), $data)) {
            return true;
        }
        return false;
    }

    /**
     * Prepare data before save
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        $this->_prepareData();
        return parent::_beforeSave();
    }

    /**
     * Prepare customer group data
     *
     * @return $this
     */
    protected function _prepareData()
    {
        $this->setCode(substr($this->getCode(), 0, self::GROUP_CODE_MAX_LENGTH));
        return $this;
    }
}
