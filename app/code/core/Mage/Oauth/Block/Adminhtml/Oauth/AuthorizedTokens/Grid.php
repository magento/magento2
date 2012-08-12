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
 * OAuth authorized tokens grid block
 *
 * @category   Mage
 * @package    Mage_Oauth
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Block_Adminhtml_Oauth_AuthorizedTokens_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Construct grid block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('authorizedTokensGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('entity_id')
            ->setDefaultDir(Varien_Db_Select::SQL_DESC);
    }

    /**
     * Prepare collection
     *
     * @return Mage_Oauth_Block_Adminhtml_Oauth_AuthorizedTokens_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Mage_Oauth_Model_Resource_Token_Collection */
        $collection = Mage::getModel('Mage_Oauth_Model_Token')->getCollection();
        $collection->joinConsumerAsApplication()
            ->addFilterByType(Mage_Oauth_Model_Token::TYPE_ACCESS);
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Mage_Oauth_Block_Adminhtml_Oauth_AuthorizedTokens_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('Mage_Oauth_Helper_Data')->__('ID'),
            'index'     => 'entity_id',
            'align'     => 'right',
            'width'     => '50px',

        ));

        $this->addColumn('name', array(
            'header'    => $this->__('Application Name'),
            'index'     => 'name',
            'escape'    => true,
        ));

        $this->addColumn('type', array(
            'header'    => $this->__('User Type'),
            //'index'     => array('customer_id', 'admin_id'),
            'options'   => array(0 => $this->__('Admin'), 1 => $this->__('Customer')),
            'frame_callback' => array($this, 'decorateUserType')
        ));

        $this->addColumn('user_id', array(
            'header'    => $this->__('User ID'),
            //'index'     => array('customer_id', 'admin_id'),
            'frame_callback' => array($this, 'decorateUserId')
        ));

        /** @var $sourceYesNo Mage_Adminhtml_Model_System_Config_Source_Yesno */
        $sourceYesNo = Mage::getSingleton('Mage_Adminhtml_Model_System_Config_Source_Yesno');
        $this->addColumn('revoked', array(
            'header'    => $this->__('Revoked'),
            'index'     => 'revoked',
            'width'     => '100px',
            'type'      => 'options',
            'options'   => $sourceYesNo->toArray(),
            'sortable'  => true,
        ));

        parent::_prepareColumns();
        return $this;
    }

    /**
     * Get grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Get revoke URL
     *
     * @param Mage_Oauth_Model_Token $row
     * @return string|null
     */
    public function getRevokeUrl($row)
    {
        return $this->getUrl('*/*/revoke', array('id' => $row->getId()));
    }

    /**
     * Get delete URL
     *
     * @param Mage_Oauth_Model_Token $row
     * @return string|null
     */
    public function getDeleteUrl($row)
    {
        return $this->getUrl('*/*/delete', array('id' => $row->getId()));
    }

    /**
     * Add mass-actions to grid
     *
     * @return Mage_Oauth_Block_Adminhtml_Oauth_AuthorizedTokens_Grid
     */
    protected function _prepareMassaction()
    {
        if(!$this->_isAllowed()) {
            return $this;
        }

        $this->setMassactionIdField('id');
        $block = $this->getMassactionBlock();

        $block->setFormFieldName('items');
        $block->addItem('enable', array(
            'label' => Mage::helper('Mage_Index_Helper_Data')->__('Enable'),
            'url'   => $this->getUrl('*/*/revoke', array('status' => 0)),
        ));
        $block->addItem('revoke', array(
            'label' => Mage::helper('Mage_Index_Helper_Data')->__('Revoke'),
            'url'   => $this->getUrl('*/*/revoke', array('status' => 1)),
        ));
        $block->addItem('delete', array(
            'label' => Mage::helper('Mage_Index_Helper_Data')->__('Delete'),
            'url'   => $this->getUrl('*/*/delete'),
        ));

        return $this;
    }

    /**
     * Decorate user type column
     *
     * @param string $value
     * @param Mage_Oauth_Model_Token $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool $isExport
     * @return mixed
     */
    public function decorateUserType($value, $row, $column, $isExport)
    {
        $options = $column->getOptions();

        $value = ($row->getCustomerId())   ?$options[1]   :$options[0];
        $cell = $value;

        return $cell;
    }

    /**
     * Decorate user type column
     *
     * @param string $value
     * @param Mage_Oauth_Model_Token $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool $isExport
     * @return mixed
     */
    public function decorateUserId($value, $row, $column, $isExport)
    {
        $value = ($row->getCustomerId())   ?$row->getCustomerId()   :$row->getAdminId();
        $cell = $value;

        return $cell;
    }

    /**
     * Check admin permissions
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        /** @var $session Mage_Backend_Model_Auth_Session */
        $session = Mage::getSingleton('Mage_Backend_Model_Auth_Session');
        return $session->isAllowed('Mage_Oauth::authorizedTokens');
    }
}
