<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class \Magento\Customer\Controller\Review
 *
 * @since 2.0.0
 */
class Review extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
