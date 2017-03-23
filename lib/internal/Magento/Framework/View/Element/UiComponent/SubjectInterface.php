<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

/**
 * Interface SubjectInterface
 */
interface SubjectInterface
{
    /**
     * Attach an observer by type
     * @param string $type
     * @param ObserverInterface $observer
     * @return void
     */
    public function attach($type, ObserverInterface $observer);

    /**
     * Detach an observer by type
     * @param string $type
     * @param ObserverInterface $observer
     * @return void
     */
    public function detach($type, ObserverInterface $observer);

    /**
     * Notify an observer(s) by type
     * @param string $type
     * @return void
     */
    public function notify($type);
}
