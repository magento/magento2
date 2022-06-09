<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\StorageInterface;
Use Magento\Framework\Exception\SessionException;
use Psr\Log\LoggerInterface;

/**
 * Clear Previous Active Sessions after Logout
 */
class ClearSessionsAfterLogoutPlugin
{
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var SaveHandlerInterface
     */
    private SaveHandlerInterface $saveHandler;

    /**
     * @var StorageInterface
     */
    private StorageInterface $storage;

    /**
     * @var State
     */
    private State $state;

    /**#@+
     * Array key for all active previous session ids.
     */
    private const PREVIOUS_ACTIVE_SESSIONS = 'previous_active_sessions';

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Initialize Dependencies
     *
     * @param Session $customerSession
     * @param SaveHandlerInterface $saveHandler
     * @param StorageInterface $storage
     * @param State $state
     * @param LoggerInterface $logger
     */
    public function __construct(
        Session $customerSession,
        SaveHandlerInterface $saveHandler,
        StorageInterface $storage,
        State $state,
        LoggerInterface $logger
    ) {
        $this->session = $customerSession;
        $this->saveHandler = $saveHandler;
        $this->storage = $storage;
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * Plugin to clear session after logout
     *
     * @param Session $subject
     * @param Session $result
     */
    public function afterLogout(Session $subject, Session $result): Session
    {
        $isAreaFrontEnd = $this->state->getAreaCode() === Area::AREA_FRONTEND;
        $previousSessions = $this->storage->getData(self::PREVIOUS_ACTIVE_SESSIONS);

        if ($isAreaFrontEnd && !empty($previousSessions)) {
            foreach ($previousSessions as $sessionId) {
                try {
                    $this->session->start();
                    $this->saveHandler->destroy($sessionId);
                    $this->session->writeClose();
                } catch (SessionException $e) {
                    $this->logger->error($e);
                }

            }
            $this->storage->setData(self::PREVIOUS_ACTIVE_SESSIONS, []);
        }
        return $result;
    }
}

