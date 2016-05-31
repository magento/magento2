<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Layout;

use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Class DepersonalizePlugin
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     */
    protected $depersonalizeChecker;

    /**
     * @var \Magento\Persistent\Model\Session
     */
    protected $persistentSession;

    /**
     * Constructor
     *
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Persistent\Model\Session $persistentSession
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
