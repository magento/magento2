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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout Update model class
 *
 * @method int getIsTemporary() getIsTemporary()
 * @method int getLayoutLinkId() getLayoutLinkId()
 * @method string getUpdatedAt() getUpdatedAt()
 * @method string getXml() getXml()
 * @method Mage_Core_Model_Layout_Update setIsTemporary() setIsTemporary(int $isTemporary)
 * @method Mage_Core_Model_Layout_Update setHandle() setHandle(string $handle)
 * @method Mage_Core_Model_Layout_Update setXml() setXml(string $xml)
 * @method Mage_Core_Model_Layout_Update setStoreId() setStoreId(int $storeId)
 * @method Mage_Core_Model_Layout_Update setThemeId() setThemeId(int $themeId)
 * @method Mage_Core_Model_Layout_Update setUpdatedAt() setUpdatedAt(string $updateDateTime)
 */
class Mage_Core_Model_Layout_Update extends Mage_Core_Model_Abstract
{
    /**
     * Layout Update model initialization
     */
    protected function _construct()
    {
        $this->_init('Mage_Core_Model_Resource_Layout_Update');
    }

    /**
     * Set current updated date
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $this->setUpdatedAt($this->getResource()->formatDate(time()));
        return parent::_beforeSave();
    }
}
