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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * description
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Rating_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('ratingsGrid');
        $this->setDefaultSort('rating_code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Mage_Rating_Model_Rating')
            ->getResourceCollection()
            ->addEntityFilter(Mage::registry('entityId'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Rating Grid colunms
     *
     * @return Mage_Adminhtml_Block_Rating_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('rating_id', array(
            'header'    => Mage::helper('Mage_Rating_Helper_Data')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'rating_id',
        ));

        $this->addColumn('rating_code', array(
            'header'    => Mage::helper('Mage_Rating_Helper_Data')->__('Rating Name'),
            'index'     => 'rating_code',
        ));

        $this->addColumn('position', array(
            'header' => Mage::helper('Mage_Rating_Helper_Data')->__('Sort Order'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'position',
        ));

        $this->addColumn('is_active', array(
            'header' => Mage::helper('Mage_Rating_Helper_Data')->__('Is Active'),
            'align' => 'left',
            'type' => 'options',
            'index' => 'is_active',
            'options'   => array(
                '1' => Mage::helper('Mage_Rating_Helper_Data')->__('Active'),
                '0' => Mage::helper('Mage_Rating_Helper_Data')->__('Inactive')
            ),
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
