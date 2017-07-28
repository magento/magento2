<?php
/**
 * Prevents path info processing for admin store
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Request;

/**
 * @api
 * @since 2.0.0
 */
class PathInfoProcessor implements \Magento\Framework\App\Request\PathInfoProcessorInterface
{
    /**
     * @var \Magento\Backend\Helper\Data
     * @since 2.0.0
     */
    private $_helper;

    /**
     * @var \Magento\Store\App\Request\PathInfoProcessor
     * @since 2.0.0
     */
    private $_subject;

    /**
     * @param \Magento\Store\App\Request\PathInfoProcessor $subject
     * @param \Magento\Backend\Helper\Data $helper
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Store\App\Request\PathInfoProcessor $subject,
        \Magento\Backend\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        $this->_subject = $subject;
    }

    /**
     * Process path info
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return string
     * @since 2.0.0
     */
    public function process(\Magento\Framework\App\RequestInterface $request, $pathInfo)
    {
        $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
        $firstPart = $pathParts[0];

        if ($firstPart != $this->_helper->getAreaFrontName()) {
            return $this->_subject->process($request, $pathInfo);
        }
        return $pathInfo;
    }
}
