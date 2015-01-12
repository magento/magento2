<?php
/**
 * Depersonalize catalog session data
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layout;

/**
 * Class DepersonalizePlugin
 */
class DepersonalizePlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $cacheConfig;

    /**
     * Catalog session
     *
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\PageCache\Model\Config $cacheConfig
     */
    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\PageCache\Model\Config $cacheConfig
    ) {
        $this->catalogSession = $catalogSession;
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * After generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @param \Magento\Framework\View\LayoutInterface $result
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && $this->cacheConfig->isEnabled()
            && !$this->request->isAjax()
            && $subject->isCacheable()
        ) {
            $this->catalogSession->clearStorage();
        }
        return $result;
    }
}
