<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Processor;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryCatalog\Model\DefaultSourceProvider;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Add default source during install
 */
class DefaultSourceProcessor
{
    /**
     * @var DefaultSourceProvider
     */
    private $defaultSourceProvider;

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param DefaultSourceProvider $defaultSourceProvider
     * @param SourceInterfaceFactory $sourceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        DefaultSourceProvider $defaultSourceProvider,
        SourceInterfaceFactory $sourceFactory,
        DataObjectHelper $dataObjectHelper,
        SourceRepositoryInterface $sourceRepository
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->sourceFactory = $sourceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * Add default source
     *
     * @return void
     */
    public function process()
    {
        $data = [
            SourceInterface::SOURCE_ID => $this->defaultSourceProvider->getId(),
            SourceInterface::NAME => 'Default Source',
            SourceInterface::ENABLED => 1,
            SourceInterface::DESCRIPTION => 'Default Source',
            SourceInterface::LATITUDE => 0,
            SourceInterface::LONGITUDE => 0,
            SourceInterface::PRIORITY => 0,
            SourceInterface::COUNTRY_ID => 'US',
            SourceInterface::POSTCODE => '00000'
        ];
        $source = $this->sourceFactory->create();
        $this->dataObjectHelper->populateWithArray($source, $data, SourceInterface::class);
        $this->sourceRepository->save($source);
    }
}
