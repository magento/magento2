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
 * Adminhtml product grid in custom options popup
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup;

use Magento\Catalog\Model\Product;

class Grid extends \Magento\Catalog\Block\Adminhtml\Product\Grid
{
    /**
     * Return empty row url for disabling JS click events
     *
     * @param Product|\Magento\Framework\Object $row
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
        $this->clearRss();
    }

    /**
     * Add import action to massaction block
     *
     * @return $this
     */
    public function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product')->addItem('import', array('label' => __('Import')));

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
