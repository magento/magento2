<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;

/**
 * Abstract class for commands related to manage Magento logs
 */
abstract class AbstractLogCommand extends Command
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Config loader
     *
     * @var ConfigLoader
     */
    private $configLoader;

    /**
     * Application state
     *
     * @var State
     */
    private $state;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param ConfigLoader $configLoader
     * @param State $state
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigLoader $configLoader,
        State $state
    ) {
        $this->objectManager = $objectManager;
        $this->configLoader = $configLoader;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * Reinitialise object manager with correct area
     *
     * @return void
     */
    protected function initApplicationArea()
    {
        $area = FrontNameResolver::AREA_CODE;
        $this->state->setAreaCode($area);
        $this->objectManager->configure($this->configLoader->load($area));
    }
}
