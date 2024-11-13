<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Data;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

/**
 * Invokes all registered processors for data fixture
 */
class CompositeProcessor implements ProcessorInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Stored Processors
     *
     * @var array
     */
    protected $processors = [];

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     */
    public function process(DataFixtureInterface $fixture, array $data): array
    {
        foreach ($this->getProcessors() as $processor) {
            $data = $this->objectManager->get($processor)->process($fixture, $data);
        }
        return $data;
    }

    /**
     * Get registered processors
     *
     * @return array
     */
    private function getProcessors(): array
    {
        return [
            UniqueIdProcessor::class
        ];
    }
}
