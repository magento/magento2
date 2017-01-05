<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Signifyd\Model\MessageGenerators\GeneratorFactory;
use Magento\Signifyd\Model\Config;

/**
 * Creates instance of case updating service configured with specific message generator.
 * The message generator initialization depends on specified type (like, case creation, re-scoring, review and
 * guarantee completion).
 */
class CaseUpdatingServiceFactory
{
    /**
     * Type of testing Signifyd case
     * @var string
     */
    private static $caseTest = 'cases/test';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * CaseUpdatingServiceFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param GeneratorFactory $generatorFactory
     * @param Config $config
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
     * @return CaseUpdatingServiceInterface
     * @throws \InvalidArgumentException
     */
    public function create($type)
    {
        if (!$this->config->isActive() || $type === self::$caseTest) {
            return $this->objectManager->create(StubCaseUpdatingService::class);
        }

        $messageGenerator = $this->generatorFactory->create($type);
        $service = $this->objectManager->create(CaseUpdatingService::class, [
            'messageGenerator' => $messageGenerator
        ]);

        return $service;
    }
}
