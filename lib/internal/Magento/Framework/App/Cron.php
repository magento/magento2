<?php
/**
 * Cron application
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App;
use Magento\Framework\ObjectManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cron implements \Magento\Framework\AppInterface
{
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
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    private $areaList;

    /**
     * Inject dependencies
     *
     * @param State $state
     * @param Console\Request $request
     * @param Console\Response $response
     * @param ObjectManagerInterface $objectManager
     * @param array $parameters
     * @param AreaList|null          $areaList
     */
    public function __construct(
        State $state,
        Console\Request $request,
        Console\Response $response,
        ObjectManagerInterface $objectManager,
        array $parameters = [],
        ?\Magento\Framework\App\AreaList $areaList = null
    ) {
        $this->_state = $state;
        $this->_request = $request;
        $this->_request->setParams($parameters);
        $this->_response = $response;
        $this->objectManager = $objectManager;
        $this->areaList = $areaList ? $areaList : $this->objectManager->get(\Magento\Framework\App\AreaList::class);
    }

    /**
     * Run application
     *
     * @return ResponseInterface
     */
    public function launch()
    {
        $this->_state->setAreaCode(Area::AREA_CRONTAB);
        $configLoader = $this->objectManager->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class);
        $this->objectManager->configure($configLoader->load(Area::AREA_CRONTAB));

        $this->areaList->getArea(Area::AREA_CRONTAB)->load(Area::PART_TRANSLATE);

        /** @var \Magento\Framework\Event\ManagerInterface $eventManager */
        $eventManager = $this->objectManager->get(\Magento\Framework\Event\ManagerInterface::class);
        $eventManager->dispatch('default');
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
