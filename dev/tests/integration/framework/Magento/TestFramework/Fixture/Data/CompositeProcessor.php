<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Data;

use Magento\Framework\ObjectManagerInterface;
/**
 * Class CompositeProcessor
 * @package Magento\TestFramework\Fixture\Data
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
     * @param array $data
     * @param $fixture
     * @return array
     */
    public function process(array &$data, $fixture)
    {
        foreach ($this->getProcessors() as $processor) {
            $this->objectManager->get($processor)->process($data, $fixture);
        }
        return $data;
    }

    /**
     * @param $fixture
     */
    public function revert($fixture)
    {
        foreach ($this->getProcessors() as $processor) {
            $this->objectManager->get($processor)->revert($fixture);
        }
    }

    /**
     * Get registered processors
     *
     * @return array
     */
    public function getProcessors(): array
    {
        return [
            UniqueIdProcessor::class
        ];
    }
}
