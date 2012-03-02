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
 * @category    Phoenix
 * @package     Phoenix_Moneybookers
 * @copyright   Copyright (c) 2012 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Phoenix_Moneybookers_MoneybookersController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Retrieve Moneybookers helper
     *
     * @return Phoenix_Moneybookers_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('Phoenix_Moneybookers_Helper_Data');
    }

    /**
     * Send activation Email to Moneybookers
     */
    public function activateemailAction()
    {
        $this->_getHelper()->activateEmail();
    }

    /**
     * Check if email is registered at Moneybookers
     */
    public function checkemailAction()
    {
        try {
            $params = $this->getRequest()->getParams();
            if (empty($params['email'])) {
                Mage::throwException('Error: No parameters specified');
            }
            $response =  $this->_getHelper()->checkEmailRequest($params);
            if (empty($response)) {
                Mage::throwException('Error: Connection to moneybookers.com failed');
            }
            $this->getResponse()->setBody($response);
            return;
        } catch (Mage_Core_Exception $e) {
            $response = $e->getMessage();
        } catch (Exception $e) {
            $response = 'Error: System error during request';
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Check if entered secret is valid
     */
    public function checksecretAction()
    {
        try {
            $params = $this->getRequest()->getParams();
            if (empty($params['email']) || empty($params['secret'])) {
                 Mage::throwException('Error: No parameters specified');
            }
            $response =  $this->_getHelper()->checkSecretRequest($params);
            if (empty($response)) {
                Mage::throwException('Error: Connection to moneybookers.com failed');
            }
            $this->getResponse()->setBody($response);
            return;
        } catch (Mage_Core_Exception $e) {
            $response = $e->getMessage();
        } catch (Exception $e) {
            $response = 'Error: System error during request';
        }
        $this->getResponse()->setBody($response);
    }
}
