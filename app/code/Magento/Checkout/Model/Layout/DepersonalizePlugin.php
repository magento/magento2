<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Layout;

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
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $checkoutSession;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
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
    public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->checkoutSession->clearStorage();
        }
        return $result;
    }
}
