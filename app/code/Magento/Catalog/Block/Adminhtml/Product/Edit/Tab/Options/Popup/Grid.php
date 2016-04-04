<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml product grid in custom options popup
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup;

use Magento\Catalog\Model\Product;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Grid extends \Magento\Catalog\Block\Adminhtml\Product\Grid
{
    /**
     * Return empty row url for disabling JS click events
     *
     * @param Product|\Magento\Framework\DataObject $row
     * @return string|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRowUrl($row)
    {
        return null;
    }

    /**
     * Remove some grid columns for product grid in popup
     *
     * @return void
     */
    public function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('action');
        $this->removeColumn('status');
        $this->removeColumn('visibility');
    }

    /**
     * Add import action to massaction block
     *
     * @return $this
     */
    public function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product')->addItem('import', ['label' => __('Import')]);

        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        if (null !== $this->getRequest()->getParam('current_product_id')) {
            $this->getCollection()->getSelect()->where(
                'e.entity_id != ?',
                $this->getRequest()->getParam('current_product_id')
            );
        }

        $this->getCollection()->getSelect()->distinct()->join(
            ['opt' => $this->getCollection()->getTable('catalog_product_option')],
            'opt.product_id = e.entity_id',
            null
        );

        return $this;
    }

    /**
     * Define grid update URL for ajax queries
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('catalog/*/optionsimportgrid');
    }
}
