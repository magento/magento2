<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Marketplace\Controller\Adminhtml\Partners;

/**
 * Class \Magento\Marketplace\Controller\Adminhtml\Partners\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Marketplace\Controller\Adminhtml\Partners
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

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
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $output = $this->getLayoutFactory()->create()
                ->createBlock(\Magento\Marketplace\Block\Partners::class)
                ->toHtml();
            $this->getResponse()->appendBody($output);
        }
    }

    /**
     * @return \Magento\Framework\View\LayoutFactory
     * @since 2.0.0
     */
    public function getLayoutFactory()
    {
        return $this->layoutFactory;
    }
}
