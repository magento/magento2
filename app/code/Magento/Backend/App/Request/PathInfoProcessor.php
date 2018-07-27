<?php
/**
 * Prevents path info processing for admin store
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Request;

class PathInfoProcessor implements \Magento\Framework\App\Request\PathInfoProcessorInterface
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    private $_helper;

    /**
     * @var \Magento\Store\App\Request\PathInfoProcessor
     */
    private $_subject;

    /**
     * @param \Magento\Store\App\Request\PathInfoProcessor $subject
     * @param \Magento\Backend\Helper\Data $helper
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
