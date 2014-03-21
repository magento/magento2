<?php
/**
 * Url Rewrite front controller plugin. Performs url rewrites
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
namespace Magento\Core\App\FrontController\Plugin;

class UrlRewrite
{
    /**
     * @var \Magento\Core\App\Request\RewriteService RewriteService
     */
    protected $_rewriteService;

    /**
     * @var \Magento\App\State
     */
    protected $_state;

    /**
     * @param \Magento\Core\App\Request\RewriteService $rewriteService
     * @param \Magento\App\State $state
     */
    public function __construct(\Magento\Core\App\Request\RewriteService $rewriteService, \Magento\App\State $state)
    {
        $this->_rewriteService = $rewriteService;
        $this->_state = $state;
    }

    /**
     * Perform url rewites
     *
     * @param \Magento\App\FrontController $subject
     * @param callable $proceed
     * @param \Magento\App\RequestInterface $request
     *
     * @return \Magento\App\ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\App\FrontController $subject,
        \Closure $proceed,
        \Magento\App\RequestInterface $request
    ) {
        if (!$this->_state->isInstalled()) {
            return $proceed($request);
        }
        $this->_rewriteService->applyRewrites($request);
        return $proceed($request);
    }
}
