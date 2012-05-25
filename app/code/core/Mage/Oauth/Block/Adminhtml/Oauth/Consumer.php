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
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * OAuth consumers grid container block
 *
 * @category   Mage
 * @package    Mage_Oauth
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Block_Adminhtml_Oauth_Consumer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Construct grid container
     */
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'Mage_Oauth';
        $this->_controller = 'adminhtml_oauth_consumer';
        $this->_headerText = Mage::helper('Mage_Adminhtml_Helper_Data')->__('OAuth Consumers');

        //check allow edit
        /** @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton('Mage_Admin_Model_Session');
        if (!$session->isAllowed('system/oauth/consumer/edit')) {
            $this->_removeButton('add');
        }
    }
}
