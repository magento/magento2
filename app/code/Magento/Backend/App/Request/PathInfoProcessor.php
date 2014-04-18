<?php
/**
 * Prevents path info processing for admin store
 *
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
        $storeCode = $pathParts[0];

        if ($storeCode != $this->_helper->getAreaFrontName()) {
            return $this->_subject->process($request, $pathInfo);
        }
        return $pathInfo;
    }
}
