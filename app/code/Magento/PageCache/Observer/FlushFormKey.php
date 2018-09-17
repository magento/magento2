<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\PageCache\FormKey as CookieFormKey;
use Magento\Framework\Data\Form\FormKey as DataFormKey;

/**
 * Flush FormKey after set of events (login/logout customer and backend).
 */
class FlushFormKey implements ObserverInterface
{
    /**
     * @var CookieFormKey
     */
    private $cookieFormKey;

    /**
     * @var DataFormKey
     */
    private $dataFormKey;

    /**
     * @param CookieFormKey $cookieFormKey
     * @param DataFormKey $dataFormKey
     */
    public function __construct(CookieFormKey $cookieFormKey, DataFormKey $dataFormKey)
    {
        $this->cookieFormKey = $cookieFormKey;
        $this->dataFormKey = $dataFormKey;
    }

    /**
     * Flush FormKey after set of events (login/logout customer and backend).
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->cookieFormKey->delete();
        $this->dataFormKey->set(null);
    }
}
