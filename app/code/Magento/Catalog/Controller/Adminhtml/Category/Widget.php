<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Framework\View\Element\BlockInterface;

/**
 * Catalog category widgets controller for CMS WYSIWYG
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
abstract class Widget extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     * @since 2.0.0
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @return BlockInterface
     * @since 2.0.0
     */
    protected function _getCategoryTreeBlock()
    {
        return $this->layoutFactory->create()
            ->createBlock(
                \Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser::class,
                '',
                [
                    'data' => [
                        'id' => $this->getRequest()->getParam('uniq_id'),
                        'use_massaction' => $this->getRequest()->getParam('use_massaction', false),
                    ]
                ]
            );
    }
}
