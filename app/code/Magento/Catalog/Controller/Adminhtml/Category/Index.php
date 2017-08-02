<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Category\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     * @since 2.0.0
     */
    protected $resultForwardFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Catalog categories index action
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
