<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Layout;

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
     * @var \Magento\Persistent\Model\Session
     * @since 2.0.0
     */
    protected $persistentSession;

    /**
     * Constructor
     *
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Persistent\Model\Session $persistentSession
     * @since 2.0.0
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        \Magento\Persistent\Model\Session $persistentSession
    ) {
        $this->persistentSession = $persistentSession;
        $this->depersonalizeChecker = $depersonalizeChecker;
    }

    /**
     * After generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @param \Magento\Framework\View\LayoutInterface $result
     * @return \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    public function afterGenerateXml(
        \Magento\Framework\View\LayoutInterface $subject,
        \Magento\Framework\View\LayoutInterface $result
    ) {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->persistentSession->setCustomerId(null);
        }

        return $result;
    }
}
