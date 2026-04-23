<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Fixture;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class Block implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        BlockInterface::IDENTIFIER => 'block%uniqid%',
        BlockInterface::TITLE => 'Block%uniqid%',
        BlockInterface::CONTENT => 'BlockContent%uniqid%',
        BlockInterface::CREATION_TIME => null,
        BlockInterface::UPDATE_TIME => null,
        'active' => true
    ];

    /**
     * @param ProcessorInterface $dataProcessor
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(
        private readonly ProcessorInterface $dataProcessor,
        private readonly ServiceFactory $serviceFactory
    ) {
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Block::DEFAULT_DATA.
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->dataProcessor->process($this, array_merge(self::DEFAULT_DATA, $data));
        $service = $this->serviceFactory->create(BlockRepositoryInterface::class, 'save');

        return $service->execute(['block' => $data]);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(BlockRepositoryInterface::class, 'deleteById');
        $service->execute(['blockId' => $data->getId()]);
    }
}
