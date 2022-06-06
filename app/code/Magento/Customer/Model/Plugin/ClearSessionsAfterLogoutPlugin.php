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

/**
 * Plugin verifies permissions using Action Name against injected (`fontend/di.xml`) rules
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
     * Initialize Dependencies
     *
     * @param Session $customerSession
     * @param SaveHandlerInterface $saveHandler
     * @param StorageInterface $storage
     * @param State $state
     */
    public function __construct(
        Session $customerSession,
        SaveHandlerInterface $saveHandler,
        StorageInterface $storage,
        State $state
    ) {
        $this->session = $customerSession;
        $this->saveHandler = $saveHandler;
        $this->storage = $storage;
        $this->state = $state;
    }

    /**
     * Initialize Dependencies
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
                $this->session->start();
                $this->saveHandler->destroy($sessionId);
                $this->session->writeClose();
            }
        }
        $this->storage->setData(self::PREVIOUS_ACTIVE_SESSIONS, []);
        return $result;

    }
}

