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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configuration data collection
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Resource_ConfigData_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Is application filter applied
     *
     * @var bool
     */
    protected $_applicationFilter = false;

    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('Mage_XmlConnect_Model_ConfigData', 'Mage_XmlConnect_Model_Resource_ConfigData');
    }

    /**
     * Add application filter
     *
     * @param  $applicationId
     * @return Mage_XmlConnect_Model_Resource_ConfigData_Collection
     */
    public function addApplicationIdFilter($applicationId)
    {
        $this->_applicationFilter = true;
        $this->getSelect()->where('application_id=?', $applicationId);
        return $this;
    }

    /**
     * Add path filter
     *
     * @param  $path
     * @param bool $like
     * @return Mage_XmlConnect_Model_Resource_ConfigData_Collection
     */
    public function addPathFilter($path, $like = true)
    {
        if ($like) {
            $this->getSelect()->where('path like ?', $path . '/%');
        } else {
            $this->getSelect()->where('path=?', $path);
        }
        return $this;
    }

    /**
     * Add category filter
     *
     * @param  $category
     * @return Mage_XmlConnect_Model_Resource_ConfigData_Collection
     */
    public function addCategoryFilter($category)
    {
        $this->getSelect()->where('category=?', $category);
        return $this;
    }

    /**
     * Add value filter
     *
     * @param  $value
     * @return Mage_XmlConnect_Model_Resource_ConfigData_Collection
     */
    public function addValueFilter($value)
    {
        $this->getSelect()->where('value=?', $value);
        return $this;
    }

    /**
     * Add filter by array
     *
     * @param array $array
     * @return Mage_XmlConnect_Model_Resource_ConfigData_Collection
     */
    public function addArrayFilter(array $array)
    {
        foreach ($array as $key => $val) {
            $method = 'add' . uc_words($key, '') . 'Filter';
            if (is_callable($this->$method($val))) {
                return $this->$method($val);
            }
        }
        return $this;
    }

    /**
     * Convert items array to array for select options
     *
     * return items array
     * array(
     *      application_id => array(
     *          category => array(
     *              path
     *          )
     *      )
     * )
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = array();
        foreach ($this as $item) {
            $appId = $item->getData('application_id');
            $category = $item->getData('category');
            $path = $item->getData('path');
            $value = $item->getData('value');

            if ($this->_applicationFilter) {
                $result[$category][$path] = $value;
            } else {
                $result[$appId][$category][$path] = $value;
            }
        }
        return $result;
    }

    /**
     * Get Application filter status
     *
     * @return boolean
     */
    public function getApplicationFilter()
    {
        return $this->_applicationFilter;
    }

    /**
     * Set Application filter status
     *
     * @param boolean $applicationFilter
     */
    public function setApplicationFilter($applicationFilter)
    {
        $this->_applicationFilter = $applicationFilter;
    }
}
