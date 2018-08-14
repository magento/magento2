<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\App\State;

use Magento\Mtf\ObjectManager;

/**
 * Provides abstract implementation for Application State Interface.
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
     * List of handlers.
     *
     * @var string[]
     */
    private $arguments;

    /**
     * Specifies whether to clean instance under test.
     *
     * @var bool
     */
    protected $isCleanInstance = false;

    /**
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
     * @inheritdoc
     */
    public function clearInstance()
    {
        //
    }
}
