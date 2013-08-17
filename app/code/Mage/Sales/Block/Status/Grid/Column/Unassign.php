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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Sales_Block_Status_Grid_Column_Unassign extends Mage_Backend_Block_Widget_Grid_Column
{
    /**
     * @var Mage_Sales_Helper_Data
     */
    protected $_helper;

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_Sales_Helper_Data $helper
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Sales_Helper_Data $helper,
        array $data = array()
    ) {
        parent::__construct ($context, $data);

        $this->_helper = $helper;
    }

    /**
     * Add decorated action to column
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return array($this, 'decorateAction');
    }

    /**
     * Decorate values to column
     *
     * @param string $value
     * @param Mage_Sales_Model_Order_Status $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool $isExport
     * @return string
     */
    public function decorateAction($value, $row, $column, $isExport)
    {
        $cell = '';
        $state = $row->getState();
        if (!empty($state)) {
            $url = $this->getUrl(
                '*/*/unassign',
                array('status' => $row->getStatus(), 'state' => $row->getState())
            );
            $label = $this->_helper->__('Unassign');
            $cell = '<a href="' . $url . '">' . $label . '</a>';
        }
        return $cell;
    }
}
