<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Request;

use Magento\Framework\App\Request\PathInfoProcessorInterface;
use Magento\Backend\Helper\Data as HelperData;
use Magento\Store\App\Request\PathInfoProcessor as AppPathInfoProcessor;
use Magento\Framework\App\RequestInterface;

/**
 * Prevents path info processing for admin store
 *
 * @api
 * @since 100.0.2
 */
class PathInfoProcessor implements PathInfoProcessorInterface
{
    /**
     * @var HelperData
     */
    private $_helper;

    /**
     * @var AppPathInfoProcessor
     */
    private $_subject;

    /**
     * @param AppPathInfoProcessor $subject
     * @param HelperData $helper
     */
    public function __construct(
        AppPathInfoProcessor $subject,
        HelperData $helper
    ) {
        $this->_helper = $helper;
        $this->_subject = $subject;
    }

    /**
     * Process path info
     *
     * @param RequestInterface $request
     * @param string $pathInfo
     * @return string
     */
    public function process(RequestInterface $request, $pathInfo)
    {
        $firstPart = $pathInfo === null ? '' :
            explode('/', ltrim($pathInfo, '/'), 2)[0];
        if ($firstPart != $this->_helper->getAreaFrontName()) {
            return $this->_subject->process($request, $pathInfo);
        }
        return $pathInfo;
    }
}
