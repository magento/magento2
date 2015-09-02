<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Selection;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;

class Grid extends \Magento\Backend\App\Action
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param Context $context
     * @param Escaper $escaper
     */
    public function __construct(Context $context, Escaper $escaper)
    {
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        return $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid',
                'adminhtml.catalog.product.edit.tab.bundle.option.search.grid'
            )->setIndex(
                $this->escaper->escapeHtml(
                    $this->getRequest()->getParam('index')
                )
            )->toHtml()
        );
    }
}
