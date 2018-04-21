<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Operation;

use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryCatalog\Model\DefaultSourceProvider;

/**
 * Create default source during installation
 */
class CreateDefaultSource
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
     * Create default source
     *
     * @return void
     */
    public function execute()
    {
        $data = [
            SourceInterface::SOURCE_CODE => $this->defaultSourceProvider->getCode(),
            SourceInterface::NAME => 'Default Source',
            SourceInterface::ENABLED => 1,
            SourceInterface::DESCRIPTION => 'Default Source',
            SourceInterface::LATITUDE => 0,
            SourceInterface::LONGITUDE => 0,
            SourceInterface::COUNTRY_ID => 'US',
            SourceInterface::POSTCODE => '00000'
        ];
        $source = $this->sourceFactory->create();
        $this->dataObjectHelper->populateWithArray($source, $data, SourceInterface::class);
        $this->sourceRepository->save($source);
    }
}
