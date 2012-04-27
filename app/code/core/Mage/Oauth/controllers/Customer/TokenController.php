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
 * @package     Mage_Oauth
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * oAuth My Applications controller
 *
 * Tab "My Applications" in the Customer Account
 *
 * @category    Mage
 * @package     Mage_Oauth
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Customer_TokenController extends Mage_Core_Controller_Front_Action
{
    /**
     * Customer session model
     *
     * @var Mage_Customer_Model_Session
     */
    protected $_session;

    /**
     * Customer session model
     *
     * @var string
     */
    protected $_sessionName = 'Mage_Customer_Model_Session';

    /**
     * Check authentication
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_session = Mage::getSingleton($this->_sessionName);
        if (!$this->_session->authenticate($this)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }

    }

    /**
     * Render grid page
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages($this->_sessionName);
        $this->renderLayout();
    }

    /**
     * Redirect to referrer URL or otherwise to index page without params
     *
     * @return Mage_Oauth_Customer_TokenController
     */
    protected function _redirectBack()
    {
        $url = $this->_getRefererUrl();
        if (Mage::app()->getStore()->getBaseUrl() == $url) {
            $url = Mage::getUrl('*/*/index');
        }
        $this->_redirectUrl($url);
        return $this;
    }

    /**
     * Update revoke status action
     */
    public function revokeAction()
    {
        $id = $this->getRequest()->getParam('id');
        $status = $this->getRequest()->getParam('status');

        if (0 === (int) $id) {
            // No ID
            $this->_session->addError($this->__('Invalid entry ID.'));
            $this->_redirectBack();
            return;
        }

        if (null === $status) {
            // No status selected
            $this->_session->addError($this->__('Invalid revoke status.'));
            $this->_redirectBack();
            return;
        }

        try {
            /** @var $collection Mage_Oauth_Model_Resource_Token_Collection */
            $collection = Mage::getModel('Mage_Oauth_Model_Token')->getCollection();
            $collection->joinConsumerAsApplication()
                    ->addFilterByCustomerId($this->_session->getCustomerId())
                    ->addFilterById($id)
                    ->addFilterByType(Mage_Oauth_Model_Token::TYPE_ACCESS)
                    ->addFilterByRevoked(!$status);
            //here is can be load from model, but used from collection for get consumer name

            /** @var $model Mage_Oauth_Model_Token */
            $model = $collection->getFirstItem();
            if ($model->getId()) {
                $name = $model->getName();
                $model->load($model->getId());
                $model->setRevoked($status)->save();
                if ($status) {
                    $message = $this->__('Application "%s" has been revoked.', $name);
                } else {
                    $message = $this->__('Application "%s" has been enabled.', $name);
                }
                $this->_session->addSuccess($message);
            } else {
                $this->_session->addError($this->__('Application not found.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_session->addError($this->__('An error occurred on update revoke status.'));
            Mage::logException($e);
        }
        $this->_redirectBack();
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');

        if (0 === (int) $id) {
            // No ID
            $this->_session->addError($this->__('Invalid entry ID.'));
            $this->_redirectBack();
            return;
        }

        try {
            /** @var $collection Mage_Oauth_Model_Resource_Token_Collection */
            $collection = Mage::getModel('Mage_Oauth_Model_Token')->getCollection();
            $collection->joinConsumerAsApplication()
                    ->addFilterByCustomerId($this->_session->getCustomerId())
                    ->addFilterByType(Mage_Oauth_Model_Token::TYPE_ACCESS)
                    ->addFilterById($id);

            /** @var $model Mage_Oauth_Model_Token */
            $model = $collection->getFirstItem();
            if ($model->getId()) {
                $name = $model->getName();
                $model->delete();
                $this->_session->addSuccess(
                    $this->__('Application "%s" has been deleted.', $name));
            } else {
                $this->_session->addError($this->__('Application not found.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_session->addError($this->__('An error occurred on delete application.'));
            Mage::logException($e);
        }
        $this->_redirectBack();
    }
}
