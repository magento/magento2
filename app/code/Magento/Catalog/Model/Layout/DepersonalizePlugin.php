<?php
/**
 * Depersonalize catalog session data
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layout;

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
     * Catalog session
     *
     * @var \Magento\Catalog\Model\Session
     * @since 2.0.0
     */
    protected $catalogSession;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @since 2.0.0
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->catalogSession = $catalogSession;
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
            $this->catalogSession->clearStorage();
        }
        return $result;
    }
}
