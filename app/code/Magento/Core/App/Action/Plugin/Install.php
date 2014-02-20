<?php
/**
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

namespace Magento\Core\App\Action\Plugin;


class Install
{
    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @param \Magento\App\State $appState
     * @param \Magento\App\ResponseInterface $response
     * @param \Magento\UrlInterface $url
     * @param \Magento\App\ActionFlag $actionFlag
     */
    public function __construct(
        \Magento\App\State $appState,
        \Magento\App\ResponseInterface $response,
        \Magento\UrlInterface $url,
        \Magento\App\ActionFlag $actionFlag
    ) {
        $this->_appState = $appState;
        $this->_response = $response;
        $this->_url = $url;
        $this->_actionFlag = $actionFlag;
    }

    /**
     * Dispatch request
     *
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     * @return mixed
     */
    public function aroundDispatch(array $arguments, \Magento\Code\Plugin\InvocationChain $invocationChain)
    {
        if (!$this->_appState->isInstalled()) {
            $this->_actionFlag->set('', \Magento\App\Action\Action::FLAG_NO_DISPATCH, true);
            $this->_response->setRedirect(
                $this->_url->getUrl('install')
            );
            return $this->_response;
        }
        return $invocationChain->proceed($arguments);
    }
}
