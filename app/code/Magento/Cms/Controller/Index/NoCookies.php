<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Index;

class NoCookies extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Render Disable cookies page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $pageId = $this->_objectManager->get(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            \Magento\Framework\Store\ScopeInterface::SCOPE_STORE
        )->getValue(
            \Magento\Cms\Helper\Page::XML_PATH_NO_COOKIES_PAGE,
            \Magento\Framework\Store\ScopeInterface::SCOPE_STORE
        );
        $resultPage = $this->_objectManager->get('Magento\Cms\Helper\Page')->prepareResultPage($this, $pageId);
        if (!$resultPage) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('defaultNoCookies');
        }
        return $resultPage;
    }
}
