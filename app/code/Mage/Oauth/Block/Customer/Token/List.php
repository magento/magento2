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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customer My Applications list block
 *
 * @category   Mage
 * @package    Mage_Oauth
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Block_Customer_Token_List extends Mage_Customer_Block_Account_Dashboard
{
    /**
     * Collection model
     *
     * @var Mage_Oauth_Model_Resource_Token_Collection
     */
    protected $_collection;

    /**
     * Prepare collection
     */
    protected function _construct()
    {
        /** @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('Mage_Customer_Model_Session');

        /** @var $collection Mage_Oauth_Model_Resource_Token_Collection */
        $collection = Mage::getModel('Mage_Oauth_Model_Token')->getCollection();
        $collection->joinConsumerAsApplication()
                ->addFilterByType(Mage_Oauth_Model_Token::TYPE_ACCESS)
                ->addFilterByCustomerId($session->getCustomerId());
        $this->_collection = $collection;
    }

    /**
     * Get count of total records
     *
     * @return int
     */
    public function count()
    {
        return $this->_collection->getSize();
    }

    /**
     * Get toolbar html
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Prepare layout
     *
     * @return Mage_Oauth_Block_Customer_Token_List
     */
    protected function _prepareLayout()
    {
        /** @var $toolbar Mage_Page_Block_Html_Pager */
        $toolbar = $this->getLayout()->createBlock('Mage_Page_Block_Html_Pager', 'customer_token.toolbar');
        $toolbar->setCollection($this->_collection);
        $this->setChild('toolbar', $toolbar);
        parent::_prepareLayout();
        return $this;
    }

    /**
     * Get collection
     *
     * @return Mage_Oauth_Model_Resource_Token_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Get link for update revoke status
     *
     * @param Mage_Oauth_Model_Token $model
     * @return string
     */
    public function getUpdateRevokeLink(Mage_Oauth_Model_Token $model)
    {
        return Mage::getUrl('oauth/customer_token/revoke/',
            array('id' => $model->getId(), 'status' => (int) !$model->getRevoked()));
    }

    /**
     * Get delete link
     *
     * @param Mage_Oauth_Model_Token $model
     * @return string
     */
    public function getDeleteLink(Mage_Oauth_Model_Token $model)
    {
        return Mage::getUrl('oauth/customer_token/delete/', array('id' => $model->getId()));
    }

    /**
     * Retrieve a token status label
     *
     * @param int $revokedStatus Token status of revoking
     * @return string
     */
    public function getStatusLabel($revokedStatus)
    {
        $labels = array(
            $this->__('Enabled'),
            $this->__('Disabled')
        );
        return $labels[$revokedStatus];
    }

    /**
     * Retrieve a label of link to change a token status
     *
     * @param int $revokedStatus Token status of revoking
     * @return string
     */
    public function getChangeStatusLabel($revokedStatus)
    {
        $labels = array(
            $this->__('Disable'),
            $this->__('Enable')
        );
        return $labels[$revokedStatus];
    }

    /**
     * Retrieve a message to confirm an action to change a token status
     *
     * @param int $revokedStatus Token status of revoking
     * @return string
     */
    public function getChangeStatusConfirmMessage($revokedStatus)
    {
        $messages = array(
            $this->__('Are you sure you want to disable this application?'),
            $this->__('Are you sure you want to enable this application?')
        );
        return $messages[$revokedStatus];
    }
}
