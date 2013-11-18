<?php
/**
 * Web API request.
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Controller;

class Request extends \Zend_Controller_Request_Http implements \Magento\App\RequestInterface
{
    /**
     * Modify pathInfo: strip down the front name and query parameters.
     *
     * @param \Magento\App\AreaList $areaList
     * @param \Magento\Config\ScopeInterface $configScope
     * @param null|string|\Zend_Uri $uri
     */
    public function __construct(
        \Magento\App\AreaList $areaList,
        \Magento\Config\ScopeInterface $configScope,
        $uri = null
    ) {
        parent::__construct($uri);
        $areaFrontName = $areaList->getFrontName($configScope->getCurrentScope());
        $this->_pathInfo = $this->_requestUri;
        /** Remove base url and area from path */
        $this->_pathInfo = preg_replace("#.*?/{$areaFrontName}/?#", '/', $this->_pathInfo);
        /** Remove GET parameters from path */
        $this->_pathInfo = preg_replace('#\?.*#', '', $this->_pathInfo);
    }
}
