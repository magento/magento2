<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Escaper;
use Magento\CatalogUrlRewrite\Model\UrlDuplicatesRegistry;

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
                    'Could not save the following URL keys for specified store because they already exist: %1',
                    $this->escaper->escapeHtml($urls)
                )
            );
        }
    }
}
