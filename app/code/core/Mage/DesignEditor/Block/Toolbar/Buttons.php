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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Exit button control block
 */
class Mage_DesignEditor_Block_Toolbar_Buttons extends Mage_Core_Block_Template
{
    /**
     * Get exit editor URL
     *
     * @return string
     */
    public function getExitUrl()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Url')->getUrl('adminhtml/system_design_editor/exit');
    }

    /**
     * Get "View Layout" button URL
     *
     * @return string
     */
    public function getViewLayoutUrl()
    {
        return $this->getUrl('design/editor/compactXml');
    }

    /**
     * Get "Compact Log" button URL
     *
     * @return string
     */
    public function getCompactLogUrl()
    {
        return $this->getUrl('design/editor/compactHistory');
    }
}
