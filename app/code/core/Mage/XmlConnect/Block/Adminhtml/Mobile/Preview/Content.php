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
 * XmlConnect preview content block
 *
 * @category   Mage
 * @package    Mage_XmlConnect
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Preview_Content extends Mage_Adminhtml_Block_Template
{
    /**
     * Set path to template used for generating block's output.
     *
     * @param string $templateType
     * @return Mage_XmlConnect_Block_Adminhtml_Mobile_Preview_Content
     */
    public function setTemplate($templateType)
    {
        $deviceType = Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceType();
        parent::setTemplate('Mage_XmlConnect::edit/tab/design/preview/' . $templateType . '_' . $deviceType . '.phtml');
        return $this;
    }
}