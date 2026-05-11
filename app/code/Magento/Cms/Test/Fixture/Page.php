<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Fixture;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class Page implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        PageInterface::IDENTIFIER => 'page%uniqid%',
        PageInterface::TITLE => 'Page%uniqid%',
        PageInterface::CONTENT => 'PageContent%uniqid%',
        PageInterface::CREATION_TIME => null,
        PageInterface::UPDATE_TIME => null,
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
        $service = $this->serviceFactory->create(PageRepositoryInterface::class, 'save');

        return $service->execute(['page' => $data]);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(PageRepositoryInterface::class, 'deleteById');
        $service->execute(['pageId' => $data->getId()]);
    }
}
