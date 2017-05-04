<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Escaper;
use Magento\UrlRewrite\Model\UrlDuplicatesRegistry;

class DuplicateUrlControllerAfterSaveObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var UrlDuplicatesRegistry
     */
    private $urlDuplicatesRegistry;

    /**
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     * @param UrlDuplicatesRegistry $urlDuplicatesRegistry
     */
    public function __construct(
        ManagerInterface $messageManager,
        Escaper $escaper,
        UrlDuplicatesRegistry $urlDuplicatesRegistry
    ) {
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
        $this->urlDuplicatesRegistry = $urlDuplicatesRegistry;
    }

    /**
     * Add url rewrite duplicates warnings
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!empty($this->urlDuplicatesRegistry->getUrlDuplicates())) {
            $urls = '';
            foreach ($this->urlDuplicatesRegistry->getUrlDuplicates() as $url) {
                /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $url */
                $urls .= $url->getRequestPath() . ', ';
            }
            $urls = rtrim($urls, ', ');
            $this->messageManager->addWarningMessage(
                __(
                    'There is a conflict between the product\'s URL keys and other URLs.'
                    . 'The product can\'t be accessed in the frontend in the specified store, through this URL: %1'
                    . 'To fix the conflict, under Search Engine Optimization, edit the URL key to make it unique.',
                    $this->escaper->escapeHtml($urls)
                )
            );
        }
    }
}
