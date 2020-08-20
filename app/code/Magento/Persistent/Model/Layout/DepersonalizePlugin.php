<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model\Layout;

use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;
use Magento\Persistent\Model\Session as PersistentSession;

/**
 * Depersonalize customer data.
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     */
    private $depersonalizeChecker;

    /**
     * @var PersistentSession
     */
    private $persistentSession;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param PersistentSession $persistentSession
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        PersistentSession $persistentSession
    ) {
        $this->depersonalizeChecker = $depersonalizeChecker;
        $this->persistentSession = $persistentSession;
    }

    /**
     * Change sensitive customer data if the depersonalization is needed.
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function afterGenerateElements(LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->persistentSession->setCustomerId(null);
        }
    }
}
