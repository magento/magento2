<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

/**
 * Interface SubjectInterface
 * @since 2.0.0
 */
interface SubjectInterface
{
    /**
     * Attach an observer by type
     * @param string $type
     * @param ObserverInterface $observer
     * @return void
     * @since 2.0.0
     */
    public function attach($type, ObserverInterface $observer);

    /**
     * Detach an observer by type
     * @param string $type
     * @param ObserverInterface $observer
     * @return void
     * @since 2.0.0
     */
    public function detach($type, ObserverInterface $observer);

    /**
     * Notify an observer(s) by type
     * @param string $type
     * @return void
     * @since 2.0.0
     */
    public function notify($type);
}
