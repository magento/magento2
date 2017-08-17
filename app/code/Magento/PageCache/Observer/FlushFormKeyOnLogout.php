<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\PageCache\FormKey;

/**
 * Class \Magento\PageCache\Observer\FlushFormKeyOnLogout
 *
 */
class FlushFormKeyOnLogout implements ObserverInterface
{
    /**
     * @var FormKey
     */
    private $cookieFormKey;

    /**
     * @param FormKey $cookieFormKey
     */
    public function __construct(
        FormKey $cookieFormKey
    ) {
        $this->cookieFormKey = $cookieFormKey;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->cookieFormKey->delete();
    }
}
