<?php
/**
 * Default no route page action
 * Used if no route page don't configure or available
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Index;

class DefaultNoRoute extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\LayoutFactory
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->setHeader('HTTP/1.1', '404 Not Found');
        $resultLayout->setHeader('Status', '404 File not found');
        return $resultLayout;
    }
}
