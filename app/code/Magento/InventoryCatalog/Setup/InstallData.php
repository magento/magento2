<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class InstallData
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param SourceRepositoryInterface $sourceRepository
     * @param SourceInterfaceFactory $sourceFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        SourceInterfaceFactory $sourceFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->sourceFactory = $sourceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->addDefaultSource();
    }

    /**
     * Add default source
     *
     * @return void
     */
    private function addDefaultSource()
    {
        $data = [
            SourceInterface::SOURCE_ID => 1,
            SourceInterface::NAME => 'Default Source',
            SourceInterface::ENABLED => 1,
            SourceInterface::DESCRIPTION => 'Default Source',
            SourceInterface::LATITUDE => 0,
            SourceInterface::LONGITUDE => 0,
            SourceInterface::PRIORITY => 0,
            SourceInterface::COUNTRY_ID => 'PL',
            SourceInterface::POSTCODE => '00-000'
        ];
        $source = $this->sourceFactory->create();
        $this->dataObjectHelper->populateWithArray($source, $data, SourceInterface::class);
        $this->sourceRepository->save($source);
    }
}
