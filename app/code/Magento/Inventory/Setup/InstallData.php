<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class InstallData
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var SourceRepositoryInterface $stockRepository
     */
    private $sourceRepository;

    /**
     * @var SourceInterface
     */
    private $source;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param SourceRepositoryInterface $sourceRepository
     * @param SourceInterface $source
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        SourceInterface $source,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->source = $source;
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
        $this->dataObjectHelper->populateWithArray($this->source, $data, SourceInterface::class);
        $this->sourceRepository->save($this->source);
    }
}

