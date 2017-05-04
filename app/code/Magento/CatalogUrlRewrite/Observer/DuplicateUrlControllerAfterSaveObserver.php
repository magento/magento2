<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Escaper;

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
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     */
    public function __construct(
        ManagerInterface $messageManager,
        Escaper $escaper
    ) {
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
    }

    /**
     * Add url collisions notices
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();
        if ($product->getData('unsaved_urls')) {
            $urls = '';
            foreach ($product->getData('unsaved_urls') as $url) {
                /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $url */
                $urls .= $url->getRequestPath() . ', ';
            }
            $urls = rtrim($urls, ', ');
            $this->messageManager->addWarningMessage(
                __(
                    'The following URL keys for specified store already exists %1',
                    $this->escaper->escapeHtml($urls)
                )
            );
        }
    }
}
