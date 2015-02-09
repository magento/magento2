<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Generic frontend controller
 */
namespace Magento\Core\App\Action\Plugin;

class LastUrl
{
    /**
     * Session namespace to refer in other places
     */
    const SESSION_NAMESPACE = 'frontend';

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Namespace for session.
     *
     * @var string
     */
    protected $_sessionNamespace = self::SESSION_NAMESPACE;

    /**
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(\Magento\Framework\Session\Generic $session, \Magento\Framework\UrlInterface $url)
    {
        $this->_session = $session;
        $this->_url = $url;
    }

    /**
     * Process request
     *
     * @param \Magento\Framework\App\Action\Action $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\Action\Action $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $result = $proceed($request);
        $this->_session->setLastUrl($this->_url->getUrl('*/*/*', ['_current' => true]));
        return $result;
    }
}
