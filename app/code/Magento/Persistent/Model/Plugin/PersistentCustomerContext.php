<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model\Plugin;

/**
 * Plugin for Magento\Framework\App\Http\Context to create new page cache variation for persistent session.
 */
class PersistentCustomerContext
{
    /**
     * Persistent session.
     *
     * @var \Magento\Persistent\Helper\Session
     */
    private $persistentSession;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession
    ) {
        $this->persistentSession = $persistentSession;
    }

    /**
     * Sets appropriate header if customer session is persistent.
     *
     * @param \Magento\Framework\App\Http\Context $subject
     * @return mixed
     */
    public function beforeGetVaryString(\Magento\Framework\App\Http\Context $subject)
    {
        if ($this->persistentSession->isPersistent()) {
            $subject->setValue('PERSISTENT', 1, 0);
        }
    }
}
