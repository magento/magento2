<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\SuggestAttributes
 *
 * @since 2.0.0
 */
class SuggestAttributes extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::attributes_attributes';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     * @since 2.0.0
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     * @since 2.0.0
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Search for attributes by part of attribute's label in admin store
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @since 2.0.0
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            $this->layoutFactory->create()
                ->createBlock(\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search::class)
                ->getSuggestedAttributes($this->getRequest()->getParam('label_part'))
        );
        return $resultJson;
    }
}
