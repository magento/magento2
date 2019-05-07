<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\ViewModel;

use Magento\Backend\Model\UrlInterface;

/**
 * Provides Forgot Password Url
 */
class ForgotPasswordUrlProvider implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     * @param UrlInterface $backendUrl
     */
    public function __construct(UrlInterface $backendUrl)
    {
        $this->backendUrl = $backendUrl;
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->backendUrl->getUrl($route, $params);
    }
}
