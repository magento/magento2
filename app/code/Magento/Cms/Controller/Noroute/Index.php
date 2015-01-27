<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Noroute;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Render CMS 404 Not found page
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->setHeader('HTTP/1.1', '404 Not Found');
        $resultForward->setHeader('Status', '404 File not found');

        $pageId = $this->_objectManager->get(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->getValue(
            \Magento\Cms\Helper\Page::XML_PATH_NO_ROUTE_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        /** @var \Magento\Cms\Helper\Page $pageHelper */
        $pageHelper = $this->_objectManager->get('Magento\Cms\Helper\Page');
        if (!$pageHelper->renderPage($this, $pageId)) {
            $resultForward->setController('index');
            $resultForward->forward('defaultNoRoute');
            return $resultForward;
        }
    }
}
