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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Xmlconnect history model
 *
 * @method Mage_XmlConnect_Model_Resource_History _getResource()
 * @method Mage_XmlConnect_Model_Resource_History getResource()
 * @method int getApplicationId()
 * @method Mage_XmlConnect_Model_History setApplicationId(int $value)
 * @method string getCreatedAt()
 * @method Mage_XmlConnect_Model_History setCreatedAt(string $value)
 * @method int getStoreId()
 * @method Mage_XmlConnect_Model_History setStoreId(int $value)
 * @method string getParams()
 * @method Mage_XmlConnect_Model_History setParams(string $value)
 * @method string getTitle()
 * @method Mage_XmlConnect_Model_History setTitle(string $value)
 * @method string getActivationKey()
 * @method Mage_XmlConnect_Model_History setActivationKey(string $value)
 * @method string getCode()
 * @method Mage_XmlConnect_Model_History setCode(string $value)
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_History extends Mage_Core_Model_Abstract
{
    /**
     * Initialize application
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_init('Mage_XmlConnect_Model_Resource_History');
    }

    /**
     * Get array of existing images
     *
     * @param int $id Application instance Id
     * @return array
     */
    public function getLastParams($id)
    {
        return $this->_getResource()->getLastParams($id);
    }
}
