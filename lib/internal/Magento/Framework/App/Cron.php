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
namespace Magento\Framework\App;

use Magento\Framework\App;
use Magento\Framework\Event\ManagerInterface;

class Cron implements \Magento\Framework\AppInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var Console\Request
     */
    protected $_request;

    /**
     * @var Console\Response
     */
    protected $_response;

    /**
     * @param ManagerInterface $eventManager
     * @param State $state
     * @param Console\Request $request
     * @param Console\Response $response
     * @param array $parameters
     */
    public function __construct(
        ManagerInterface $eventManager,
        State $state,
        Console\Request $request,
        Console\Response $response,
        array $parameters = array()
    ) {
        $this->_eventManager = $eventManager;
        $this->_state = $state;
        $this->_request = $request;
        $this->_request->setParam($parameters);
        $this->_response = $response;
    }

    /**
     * Run application
     *
     * @return ResponseInterface
     */
    public function launch()
    {
        $this->_state->setAreaCode('crontab');
        $this->_eventManager->dispatch('default');
        $this->_response->setCode(0);
        return $this->_response;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
