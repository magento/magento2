<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
