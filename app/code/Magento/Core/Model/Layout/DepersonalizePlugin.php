<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Core\Model\Layout;

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
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $cacheConfig;

    /**
     * @var \Magento\Framework\Message\Session
     */
    protected $messageSession;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\PageCache\Model\Config $cacheConfig
     * @param \Magento\Framework\Message\Session $messageSession
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\PageCache\Model\Config $cacheConfig,
        \Magento\Framework\Message\Session $messageSession
    ) {
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        $this->eventManager = $eventManager;
        $this->cacheConfig = $cacheConfig;
        $this->messageSession = $messageSession;
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
            $this->eventManager->dispatch('depersonalize_clear_session');
            session_write_close();
            $this->messageSession->clearStorage();
        }
        return $result;
    }
}
