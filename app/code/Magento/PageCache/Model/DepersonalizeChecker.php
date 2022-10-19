<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model;

/**
 * Checks if session should be depersonalized in Depersonalize plugin
 *
 * @api
 */
class DepersonalizeChecker
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var Config
     */
    private $cacheConfig;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param Config $cacheConfig
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Module\Manager $moduleManager,
        Config $cacheConfig
    ) {
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * Check if depersonalize or not
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @return bool
     */
    public function checkIfDepersonalize(\Magento\Framework\View\LayoutInterface $subject)
    {
        return ($this->moduleManager->isEnabled('Magento_PageCache')
            && $this->cacheConfig->isEnabled()
            && !$this->request->isAjax()
            && ($this->request->isGet() || $this->request->isHead())
            && $subject->isCacheable());
    }
}
