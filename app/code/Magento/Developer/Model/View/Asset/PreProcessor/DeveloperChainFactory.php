<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\View\Asset\PreProcessor;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\PreProcessor\ChainFactory;
use Magento\Framework\View\Asset\PreProcessor\ChainFactoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Developer\Model\Config\Source\WorkflowType;

class DeveloperChainFactory implements ChainFactoryInterface
{
    /**
     * Name of entity to create
     */
    const ENTITY_NAME = 'Magento\Developer\Model\View\Asset\PreProcessor\DeveloperChain';

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ChainFactory
     */
    private $chainFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ChainFactory $chainFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ChainFactory $chainFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->objectManager = $objectManager;
        $this->chainFactory = $chainFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {inheritdoc}
     */
    public function create(array $arguments = [])
    {
        if (WorkflowType::CLIENT_SIDE_COMPILATION === $this->scopeConfig->getValue(WorkflowType::CONFIG_NAME_PATH)) {
            return $this->objectManager->create(
                self::ENTITY_NAME,
                $arguments
            );
        }
        return $this->chainFactory->create($arguments);
    }
}
