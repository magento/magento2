<?php
/**
 * Functional limitation for number of stores
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Store_Limitation
{
    /**
     * @var Mage_Core_Model_Resource_Store
     */
    private $_resource;

    /**
     * @var int
     */
    private $_allowedQty = 0;

    /**
     * @var bool
     */
    private $_isRestricted = false;

    /**
     * Determine restriction
     *
     * @param Mage_Core_Model_Resource_Store $resource
     * @param Mage_Core_Model_Config $config
     */
    public function __construct(Mage_Core_Model_Resource_Store $resource, Mage_Core_Model_Config $config)
    {
        $this->_resource = $resource;
        $allowedQty = (string)$config->getNode('global/functional_limitation/max_store_count');
        if ('' === $allowedQty) {
            return;
        }
        $this->_allowedQty = (int)$allowedQty;
        $this->_isRestricted = true;
    }

    /**
     * Whether it is permitted to create new items
     *
     * @return bool
     */
    public function canCreate()
    {
        if ($this->_isRestricted) {
            return $this->_resource->countAll() < $this->_allowedQty;
        }
        return true;
    }

    /**
     * User notification message about the restriction
     *
     * @return string
     */
    public static function getCreateRestrictionMessage()
    {
        return Mage::helper('Mage_Core_Helper_Data')->__('You are using the maximum number of store views allowed.');
    }
}
