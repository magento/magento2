<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Controller\Adminhtml\Source;

use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Populate Source by data. Specified for form structure
 *
 * @api
 */
class SourceHydrator
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var SourceCarrierDataProcessor
     */
    private $sourceCarrierDataProcessor;

    /**
     * @var SourceRegionDataProcessor
     */
    private $sourceRegionDataProcessor;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param SourceCarrierDataProcessor $sourceCarrierDataProcessor
     * @param SourceRegionDataProcessor $sourceRegionDataProcessor
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        SourceCarrierDataProcessor $sourceCarrierDataProcessor,
        SourceRegionDataProcessor $sourceRegionDataProcessor
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sourceCarrierDataProcessor = $sourceCarrierDataProcessor;
        $this->sourceRegionDataProcessor = $sourceRegionDataProcessor;
    }

    /**
     * @param SourceInterface $source
     * @param array $data
     *
     * @return SourceInterface
     */
    public function hydrate(SourceInterface $source, array $data): SourceInterface
    {
        $data['general'] = $this->sourceCarrierDataProcessor->process($data['general']);
        $data['general'] = $this->sourceRegionDataProcessor->process($data['general']);

        $this->dataObjectHelper->populateWithArray($source, $data['general'], SourceInterface::class);

        return $source;
    }
}
