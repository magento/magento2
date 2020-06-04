<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swagger\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Result\PageFactory as PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var PageConfig
     */
    private $pageConfig;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @param Context $context
     * @param PageConfig $pageConfig
     * @param PageFactory $pageFactory
     */
    public function __construct(Context $context, PageConfig $pageConfig, PageFactory $pageFactory)
    {
        parent::__construct($context);
        $this->pageConfig = $pageConfig;
        $this->pageFactory = $pageFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->pageConfig->addBodyClass('swagger-section');
        return $this->pageFactory->create();
    }
}
