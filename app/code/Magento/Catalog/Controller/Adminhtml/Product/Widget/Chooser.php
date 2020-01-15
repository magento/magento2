<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Widget;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser\Container;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Escaper;
use Magento\Framework\View\LayoutFactory;

/**
 * Controller to build Chooser container.
 */
class Chooser extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        Escaper $escaper
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->escaper = $escaper;
    }

    /**
     * Chooser Source action.
     *
     * @return Raw
     */
    public function execute()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $massAction = $this->getRequest()->getParam('use_massaction', false);
        $productTypeId = $this->getRequest()->getParam('product_type_id', null);

        $layout = $this->layoutFactory->create();
        $productsGrid = $layout->createBlock(
            \Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser::class,
            '',
            [
                'data' => [
                    'id' => $this->escaper->escapeHtml($uniqId),
                    'use_massaction' => $massAction,
                    'product_type_id' => $productTypeId,
                    'category_id' => (int)$this->getRequest()->getParam('category_id'),
                ],
            ]
        );

        $html = $productsGrid->toHtml();

        if (!$this->getRequest()->getParam('products_grid')) {
            $categoriesTree = $layout->createBlock(
                \Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser::class,
                '',
                [
                    'data' => [
                        'id' => $this->escaper->escapeHtml($uniqId) . 'Tree',
                        'node_click_listener' => $productsGrid->getCategoryClickListenerJs(),
                        'with_empty_node' => true,
                    ],
                ]
            );

            $html = $layout->createBlock(Container::class)
                ->setTreeHtml($categoriesTree->toHtml())
                ->setGridHtml($html)
                ->toHtml();
        }

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents($html);
    }
}
