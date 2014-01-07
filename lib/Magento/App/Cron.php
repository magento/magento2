<?php
/**
 * Cron application
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
namespace Magento\App;

use \Magento\Config\ScopeInterface,
    \Magento\App\ObjectManager\ConfigLoader,
    \Magento\Event\ManagerInterface;

class Cron implements \Magento\AppInterface
{
    /**
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @param ManagerInterface $eventManager
     * @param State $state
     */
    public function __construct(
        ManagerInterface $eventManager,
        State $state
    ) {
        $this->_eventManager = $eventManager;
        $this->_state = $state;
    }

    /**
     * Execute application
     *
     * @return int
     */
    public function execute()
    {
        $this->_state->setAreaCode('crontab');
        $this->_eventManager->dispatch('default');
        return 0;
    }
}
