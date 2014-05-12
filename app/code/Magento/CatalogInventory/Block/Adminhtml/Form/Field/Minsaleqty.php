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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml catalog inventory "Minimum Qty Allowed in Shopping Cart" field
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogInventory\Block\Adminhtml\Form\Field;

class Minsaleqty extends \Magento\Backend\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var Customergroup
     */
    protected $_groupRenderer;

    /**
     * Retrieve group column renderer
     *
     * @return Customergroup
     */
    protected function _getGroupRenderer()
    {
        if (!$this->_groupRenderer) {
            $this->_groupRenderer = $this->getLayout()->createBlock(
                'Magento\CatalogInventory\Block\Adminhtml\Form\Field\Customergroup',
                '',
                array('data' => array('is_render_to_js_template' => true))
            );
            $this->_groupRenderer->setClass('customer_group_select');
        }
        return $this->_groupRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'customer_group_id',
            array('label' => __('Customer Group'), 'renderer' => $this->_getGroupRenderer())
        );
        $this->addColumn('min_sale_qty', array('label' => __('Minimum Qty')));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Minimum Qty');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\Object $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('customer_group_id')),
            'selected="selected"'
        );
    }
}
