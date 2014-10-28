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

namespace Magento\CatalogInventory\Helper;

use Magento\Store\Model\Store;
use Magento\Customer\Service\V1\CustomerGroupServiceInterface as CustomerGroupService;

/**
 * MinSaleQty value manipulation helper
 */
class Minsaleqty
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->mathRandom = $mathRandom;
    }

    /**
     * Retrieve fixed qty value
     *
     * @param int|float|string|null $qty
     * @return float|null
     */
    protected function fixQty($qty)
    {
        return !empty($qty) ? (float) $qty : null;
    }

    /**
     * Generate a storable representation of a value
     *
     * @param int|float|string|array $value
     * @return string
     */
    protected function serializeValue($value)
    {
        if (is_numeric($value)) {
            $data = (float) $value;
            return (string) $data;
        } elseif (is_array($value)) {
            $data = array();
            foreach ($value as $groupId => $qty) {
                if (!array_key_exists($groupId, $data)) {
                    $data[$groupId] = $this->fixQty($qty);
                }
            }
            if (count($data) == 1 && array_key_exists(CustomerGroupService::CUST_GROUP_ALL, $data)) {
                return (string) $data[CustomerGroupService::CUST_GROUP_ALL];
            }
            return serialize($data);
        } else {
            return '';
        }
    }

    /**
     * Create a value from a storable representation
     *
     * @param int|float|string $value
     * @return array
     */
    protected function unserializeValue($value)
    {
        if (is_numeric($value)) {
            return array(CustomerGroupService::CUST_GROUP_ALL => $this->fixQty($value));
        } elseif (is_string($value) && !empty($value)) {
            return unserialize($value);
        } else {
            return array();
        }
    }

    /**
     * Check whether value is in form retrieved by _encodeArrayFieldValue()
     *
     * @param string|array $value
     * @return bool
     */
    protected function isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('customer_group_id', $row)
                || !array_key_exists('min_sale_qty', $row)
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Encode value to be used in \Magento\Backend\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    protected function encodeArrayFieldValue(array $value)
    {
        $result = array();
        foreach ($value as $groupId => $qty) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = array('customer_group_id' => $groupId, 'min_sale_qty' => $this->fixQty($qty));
        }
        return $result;
    }

    /**
     * Decode value from used in \Magento\Backend\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    protected function decodeArrayFieldValue(array $value)
    {
        $result = array();
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('customer_group_id', $row)
                || !array_key_exists('min_sale_qty', $row)
            ) {
                continue;
            }
            $groupId = $row['customer_group_id'];
            $qty = $this->fixQty($row['min_sale_qty']);
            $result[$groupId] = $qty;
        }
        return $result;
    }

    /**
     * Retrieve min_sale_qty value from config
     *
     * @param int $customerGroupId
     * @param null|string|bool|int|Store $store
     * @return float|null
     */
    public function getConfigValue($customerGroupId, $store = null)
    {
        $value = $this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Stock\Item::XML_PATH_MIN_SALE_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        $value = $this->unserializeValue($value);
        if ($this->isEncodedArrayFieldValue($value)) {
            $value = $this->decodeArrayFieldValue($value);
        }
        $result = null;
        foreach ($value as $groupId => $qty) {
            if ($groupId == $customerGroupId) {
                $result = $qty;
                break;
            } elseif ($groupId == CustomerGroupService::CUST_GROUP_ALL) {
                $result = $qty;
            }
        }
        return $this->fixQty($result);
    }

    /**
     * Make value readable by \Magento\Backend\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param string|array $value
     * @return array
     */
    public function makeArrayFieldValue($value)
    {
        $value = $this->unserializeValue($value);
        if (!$this->isEncodedArrayFieldValue($value)) {
            $value = $this->encodeArrayFieldValue($value);
        }
        return $value;
    }

    /**
     * Make value ready for store
     *
     * @param string|array $value
     * @return string
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->isEncodedArrayFieldValue($value)) {
            $value = $this->decodeArrayFieldValue($value);
        }
        $value = $this->serializeValue($value);
        return $value;
    }
}
