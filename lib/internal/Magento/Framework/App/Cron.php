<?php
/**
 * Cron application
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        array $parameters = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_state = $state;
        $this->_request = $request;
        $this->_request->setParams($parameters);
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
