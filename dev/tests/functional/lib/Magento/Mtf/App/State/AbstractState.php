<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\App\State;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig;
use Magento\Mtf\ObjectManager;

/**
 * Abstract class AbstractState
 *
 */
abstract class AbstractState implements StateInterface
{
    /**
     * Object Manager.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * List of handlers
     *
     * @var string[]
     */
    private $arguments;

    /**
     * Specifies whether to clean instance under test
     *
     * @var bool
     */
    protected $isCleanInstance = false;

    /**
     * AbstractState constructor.
     *
     * @param ObjectManager $objectManager
     * @param array $arguments
     */
    public function __construct(
        ObjectManager $objectManager,
        array $arguments = []
    ) {
        $this->objectManager = $objectManager;
        $this->arguments = $arguments;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        foreach ($this->arguments as $argument) {
            $handler = $this->objectManager->get($argument);
            $handler->execute($this);
        }
        if ($this->isCleanInstance) {
            $this->clearInstance();
        }
    }

    /**
     * Clear Magento instance: remove all tables in DB and use dump to load new ones, clear Magento cache
     */
    public function clearInstance()
    {
        //
    }
}
