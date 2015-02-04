<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\App\Action\Plugin;

class Design
{
    /**
     * @var \Magento\Framework\View\DesignLoader
     */
    protected $_designLoader;

    /**
     * @param \Magento\Framework\View\DesignLoader $designLoader
     */
    public function __construct(\Magento\Framework\View\DesignLoader $designLoader)
    {
        $this->_designLoader = $designLoader;
    }

    /**
     * Initialize design
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
        $this->_designLoader->load();
        return $proceed($request);
    }
}
