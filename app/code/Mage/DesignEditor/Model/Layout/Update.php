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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * VDE Layout Update model class
 *
 * @method string getIsVde() getIsVde()
 * @method Mage_DesignEditor_Model_Layout_Update setIsVde() setIsVde(string $flag)
 */
class Mage_DesignEditor_Model_Layout_Update extends Mage_Core_Model_Layout_Update
{
    /**
     * Layout Update model initialization
     */
    protected function _construct()
    {
        $this->_init('Mage_DesignEditor_Model_Resource_Layout_Update');
    }

    /**
     * Set true for flag is_vde
     *
     * @return Mage_DesignEditor_Model_Layout_Update
     */
    protected function _beforeSave()
    {
        $this->setIsVde(true);
        return parent::_beforeSave();
    }
}
