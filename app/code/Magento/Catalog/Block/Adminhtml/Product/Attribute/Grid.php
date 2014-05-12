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
 * Product attributes grid
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute;

use Magento\Eav\Block\Adminhtml\Attribute\Grid\AbstractGrid;

class Grid extends AbstractGrid
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory,
        array $data = array()
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_module = 'catalog';
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Prepare product attributes grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create()->addVisibleFilter();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare product attributes grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter(
            'is_visible',
            array(
                'header' => __('Visible'),
                'sortable' => true,
                'index' => 'is_visible_on_front',
                'type' => 'options',
                'options' => array('1' => __('Yes'), '0' => __('No')),
                'align' => 'center'
            ),
            'frontend_label'
        );

        $this->addColumnAfter(
            'is_global',
            array(
                'header' => __('Scope'),
                'sortable' => true,
                'index' => 'is_global',
                'type' => 'options',
                'options' => array(
                    \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE => __('Store View'),
                    \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_WEBSITE => __('Web Site'),
                    \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL => __('Global')
                ),
                'align' => 'center'
            ),
            'is_visible'
        );

        $this->addColumn(
            'is_searchable',
            array(
                'header' => __('Searchable'),
                'sortable' => true,
                'index' => 'is_searchable',
                'type' => 'options',
                'options' => array('1' => __('Yes'), '0' => __('No')),
                'align' => 'center'
            ),
            'is_user_defined'
        );

        $this->_eventManager->dispatch('product_attribute_grid_build', array('grid' => $this));

        $this->addColumnAfter(
            'is_comparable',
            array(
                'header' => __('Comparable'),
                'sortable' => true,
                'index' => 'is_comparable',
                'type' => 'options',
                'options' => array('1' => __('Yes'), '0' => __('No')),
                'align' => 'center'
            ),
            'is_filterable'
        );

        return $this;
    }
}
