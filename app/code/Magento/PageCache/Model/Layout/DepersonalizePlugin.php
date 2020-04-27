<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\Layout;

use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Message\Session as MessageSession;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Depersonalize customer data.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     */
    private $depersonalizeChecker;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var MessageSession
     */
    private $messageSession;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param EventManager $eventManager
     * @param MessageSession $messageSession
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        EventManager $eventManager,
        MessageSession $messageSession
    ) {
        $this->depersonalizeChecker = $depersonalizeChecker;
        $this->eventManager = $eventManager;
        $this->messageSession = $messageSession;
    }

    /**
     * Change sensitive customer data if the depersonalization is needed
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function afterGenerateElements(LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->eventManager->dispatch('depersonalize_clear_session');
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            session_write_close();
            $this->messageSession->clearStorage();
        }
    }
}
