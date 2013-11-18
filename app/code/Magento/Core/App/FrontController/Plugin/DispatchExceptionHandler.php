<?php
/**
 * Dispatch exception handler
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
namespace Magento\Core\App\FrontController\Plugin;

use Magento\Core\Model\StoreManager,
    Magento\App\Dir;

class DispatchExceptionHandler
{
    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;


    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * @param StoreManager $storeManager
     * @param Dir $dir
     */
    public function __construct(
        StoreManager $storeManager,
        Dir $dir
    ) {
        $this->_storeManager = $storeManager;
        $this->_dir = $dir;
    }

    /**
     * Handle dispatch exceptions
     *
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     * @return mixed
     */
    public function aroundDispatch(array $arguments, \Magento\Code\Plugin\InvocationChain $invocationChain)
    {
        try {
            return $invocationChain->proceed($arguments);
        } catch (\Magento\Core\Model\Session\Exception $e) {
            header('Location: ' . $this->_storeManager->getStore()->getBaseUrl());
            exit;
        } catch (\Magento\Core\Model\Store\Exception $e) {
            require $this->_dir->getDir(Dir::PUB) . DS . 'errors' . DS . '404.php';
            exit;
        }
    }
}
