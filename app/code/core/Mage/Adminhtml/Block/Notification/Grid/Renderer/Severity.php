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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Notification_Grid_Renderer_Severity
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $notice = Mage::getSingleton('Mage_AdminNotification_Model_Inbox');

        switch ($row->getData($this->getColumn()->getIndex())) {
            case Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL:
                $class = 'critical';
                $value = $notice->getSeverities(Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL);
                break;
            case Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR:
                $class = 'major';
                $value = $notice->getSeverities(Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR);
                break;
            case Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR:
                $class = 'minor';
                $value = $notice->getSeverities(Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR);
                break;
            case Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE:
                $class = 'notice';
                $value = $notice->getSeverities(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
                break;
        }
        return '<span class="grid-severity-' . $class . '"><span>' . $value . '</span></span>';
    }
}
