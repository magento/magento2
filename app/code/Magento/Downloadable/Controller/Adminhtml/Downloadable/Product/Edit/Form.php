<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit;

/**
 * Class Form
 *
 * @deprecated 100.3.1 since downloadable information rendering moved to UI components.
 * @see \Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Composite
 * @package Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit
 */
class Form extends \Magento\Catalog\Controller\Adminhtml\Product\Edit
{
    /**
     * Load downloadable tab fieldsets
     *
     * @return void
     */
    public function execute()
    {
        $this->_initProduct();
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                \Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable::class,
                'admin.product.downloadable.information'
            )->toHtml()
        );
    }
}
