<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\StorageInterface;

/**
 * Observer to clear all previous sessions on Logout
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ClearPreviousSessionsObserver implements ObserverInterface
{

    /**
     * @var State
     */
    private State $state;

    /**#@+
     * Array key for all active previous session ids.
     */
    private const PREVIOUS_ACTIVE_SESSIONS = 'previous_active_sessions';

    /**
     * @var StorageInterface
     */
    private StorageInterface $storage;

    /**
     * @var SaveHandlerInterface
     */
    private SaveHandlerInterface $saveHandler;

    /**
     * @var Session
     */
    private Session $session;

    /**
     *
     * Initialize dependencies.
     *
     * @param State $state
     * @param StorageInterface $storage
     * @param SaveHandlerInterface $saveHandler
     * @param Session $session
     */
    public function __construct(
        State $state,
        StorageInterface $storage,
        SaveHandlerInterface $saveHandler,
        Session $session
    ) {
        $this->state = $state;
        $this->storage = $storage;
        $this->saveHandler = $saveHandler;
        $this->session = $session;
    }

    /**
     * Destroy all previous sessions
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $isAreaFrontEnd = $this->state->getAreaCode() === Area::AREA_FRONTEND;
        $previousSessions = $this->storage->getData(self::PREVIOUS_ACTIVE_SESSIONS);

        if (!$isAreaFrontEnd || empty($previousSessions)) {
            return;
        }

        foreach($previousSessions as $sessionId){
            $this->session->start();
            $this->saveHandler->destroy($sessionId);
            $this->session->writeClose();
        }

    }
}
