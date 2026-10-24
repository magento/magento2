<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\PageCache\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\PageCache\FormKey as CookieFormKey;
use Magento\Framework\Data\Form\FormKey as DataFormKey;

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
