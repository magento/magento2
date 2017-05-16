<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Observer;

class DuplicateEntityAdminMessageHandler implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     * DuplicateEntityAdminMessageHandler constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->messageManager = $messageManager;
        $this->backendUrl = $backendUrl;
    }


    /**
     * Adds custom error message from the custom url rewrites exception
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $exception = $observer->getException();
        
        if ($exception instanceof \Magento\UrlRewrite\Model\Storage\UrlAlreadyExistsException) {
            $generatedUrls = [];
            foreach ($exception->getUrls() as $id => $url) {
                $adminEditUrl = $this->backendUrl->getUrl(
                    'adminhtml/url_rewrite/edit',
                    ['id' => $id]
                );
                $generatedUrls[$adminEditUrl] = $url->getRequestPath();
            }
            $this->messageManager->addComplexErrorMessage(
                'urlDuplicateMessage',
                ['urls' => $generatedUrls]
            );
        }
    }
}
