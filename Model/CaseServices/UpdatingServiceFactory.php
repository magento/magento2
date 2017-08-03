<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\CaseServices;

use Magento\Framework\ObjectManagerInterface;
use Magento\Signifyd\Model\MessageGenerators\GeneratorFactory;
use Magento\Signifyd\Model\Config;

/**
 * Creates instance of case updating service configured with specific message generator.
 * The message generator initialization depends on specified type (like, case creation, re-scoring, review and
 * guarantee completion).
 * @since 2.2.0
 */
class UpdatingServiceFactory
{
    /**
     * Type of testing Signifyd case
     * @var string
     * @since 2.2.0
     */
    private static $caseTest = 'cases/test';

    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @var GeneratorFactory
     * @since 2.2.0
     */
    private $generatorFactory;

    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * UpdatingServiceFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param GeneratorFactory $generatorFactory
     * @param Config $config
     * @since 2.2.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        GeneratorFactory $generatorFactory,
        Config $config
    ) {
        $this->objectManager = $objectManager;
        $this->generatorFactory = $generatorFactory;
        $this->config = $config;
    }

    /**
     * Creates instance of service updating case.
     * As param retrieves type of message generator.
     *
     * @param string $type
     * @return UpdatingServiceInterface
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    public function create($type)
    {
        if (!$this->config->isActive() || $type === self::$caseTest) {
            return $this->objectManager->create(StubUpdatingService::class);
        }

        $messageGenerator = $this->generatorFactory->create($type);
        $service = $this->objectManager->create(UpdatingService::class, [
            'messageGenerator' => $messageGenerator
        ]);

        return $service;
    }
}
