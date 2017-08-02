<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Layout;

use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Class DepersonalizePlugin
 * @since 2.0.0
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     * @since 2.0.0
     */
    protected $depersonalizeChecker;

    /**
     * @var \Magento\Framework\Event\Manager
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Message\Session
     * @since 2.0.0
     */
    protected $messageSession;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\Message\Session $messageSession
     * @since 2.0.0
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Message\Session $messageSession
    ) {
        $this->depersonalizeChecker = $depersonalizeChecker;
        $this->eventManager = $eventManager;
        $this->messageSession = $messageSession;
    }

    /**
     * After generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @param \Magento\Framework\View\LayoutInterface $result
     * @return \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->eventManager->dispatch('depersonalize_clear_session');
            session_write_close();
            $this->messageSession->clearStorage();
        }
        return $result;
    }
}
