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
 * @since 2.0.0
 */
class FlushFormKeyOnLogout implements ObserverInterface
{
    /**
     * @var FormKey
     * @since 2.0.0
     */
    private $cookieFormKey;

    /**
     * @param FormKey $cookieFormKey
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->cookieFormKey->delete();
    }
}
