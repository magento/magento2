<?php
/**
 * Application installation plugin. Should be used by applications that require module install/upgrade.
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
namespace Magento\Module\FrontController\Plugin;

use Magento\Cache\FrontendInterface;
use Magento\Module\UpdaterInterface;
use Magento\App\State;
use Magento\Code\Plugin\InvocationChain;

class Install
{
    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var FrontendInterface
     */
    protected $_cache;

    /**
     * @var UpdaterInterface
     */
    protected $_updater;

    /**
     * @param State $appState
     * @param FrontendInterface $cache
     * @param UpdaterInterface $dbUpdater
     */
    public function __construct(
        State $appState,
        FrontendInterface $cache,
        UpdaterInterface $dbUpdater
    ) {
        $this->_appState = $appState;
        $this->_cache = $cache;
        $this->_dbUpdater = $dbUpdater;
    }

    /**
     * @param array $arguments
     * @param InvocationChain $invocationChain
     * @return mixed
     */
    public function aroundDispatch($arguments, InvocationChain $invocationChain)
    {
        if ($this->_appState->isInstalled() && !$this->_cache->load('data_upgrade')) {
            $this->_dbUpdater->updateScheme();
            $this->_dbUpdater->updateData();
            $this->_cache->save('true', 'data_upgrade');
        }
        return $invocationChain->proceed($arguments);
    }
}
