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
namespace Magento\Eav\Block\Adminhtml\Attribute\Grid;

/**
 * Product attributes grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Block Module
     *
     * @var string
     */
    protected $_module = 'adminhtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('attributeGrid');
        $this->setDefaultSort('attribute_code');
        $this->setDefaultDir('ASC');
    }

    /**
     * Prepare default grid column
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'attribute_code',
            array(
                'header' => __('Attribute Code'),
                'sortable' => true,
                'index' => 'attribute_code',
                'header_css_class' => 'col-attr-code',
                'column_css_class' => 'col-attr-code'
            )
        );

        $this->addColumn(
            'frontend_label',
            array(
                'header' => __('Attribute Label'),
                'sortable' => true,
                'index' => 'frontend_label',
                'header_css_class' => 'col-label',
                'column_css_class' => 'col-label'
            )
        );

        $this->addColumn(
            'is_required',
            array(
                'header' => __('Required'),
                'sortable' => true,
                'index' => 'is_required',
                'type' => 'options',
                'options' => array('1' => __('Yes'), '0' => __('No')),
                'header_css_class' => 'col-required',
                'column_css_class' => 'col-required'
            )
        );

        $this->addColumn(
            'is_user_defined',
            array(
                'header' => __('System'),
                'sortable' => true,
                'index' => 'is_user_defined',
                'type' => 'options',
                'options' => array(
                    '0' => __('Yes'),   // intended reverted use
                    '1' => __('No'),    // intended reverted use
                ),
                'header_css_class' => 'col-system',
                'column_css_class' => 'col-system'
            )
        );

        return $this;
    }

    /**
     * Return url of given row
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl($this->_module . '/*/edit', array('attribute_id' => $row->getAttributeId()));
    }
}
