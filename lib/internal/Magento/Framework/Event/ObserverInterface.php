<?php
/**
 * Observer interface
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Event;

/**
 * Interface \Magento\Framework\Event\ObserverInterface
 *
 * @api
 * @since 100.0.2
 */
interface ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer);
}
