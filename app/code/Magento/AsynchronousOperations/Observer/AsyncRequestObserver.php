<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\AsynchronousOperations\Registry\AsyncRequestData;

class AsyncRequestObserver implements ObserverInterface
{
    /**
     * @var AsyncRequestData
     */
    private AsyncRequestData $asyncRequestData;

    /**
     * @param AsyncRequestData $asyncRequestData
     */
    public function __construct(AsyncRequestData $asyncRequestData)
    {
        $this->asyncRequestData = $asyncRequestData;
    }

    /**
     * Handle async request authorization
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer): void
    {
        $isAsyncAuthorized = $observer->getData('isAsyncAuthorized');
        $this->asyncRequestData->setAuthorized($isAsyncAuthorized);
    }
}
