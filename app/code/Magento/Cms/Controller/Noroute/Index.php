<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Controller\Noroute;

use Magento\Cms\Helper\Page;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Forward;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ForwardFactory
     */
    protected ForwardFactory $resultForwardFactory;

    /**
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Render CMS 404 Not found page
     *
     * @return ResultInterface|Forward
     */
    public function execute(): ResultInterface|Forward
    {
        $pageId = $this->_objectManager->get(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->getValue(
            Page::XML_PATH_NO_ROUTE_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        /** @var Page $pageHelper */
        $pageHelper = $this->_objectManager->get(Page::class);
        $resultPage = $pageHelper->prepareResultPage($this, $pageId);
        if ($resultPage) {
            $resultPage->setStatusHeader(404, '1.1', 'Not Found');
            $resultPage->setHeader('Status', '404 File not found');
            $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
            return $resultPage;
        } else {
            /** @var Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->setController('index');
            $resultForward->forward('defaultNoRoute');
            return $resultForward;
        }
    }
}
