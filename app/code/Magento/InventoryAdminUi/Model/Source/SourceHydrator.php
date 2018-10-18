<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Model\Source;

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
     * @var SourceRegionDataProcessor
     */
    private $sourceRegionDataProcessor;

    /**
     * @var SourceCoordinatesDataProcessor
     */
    private $sourceCoordinatesDataProcessor;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param SourceRegionDataProcessor $sourceRegionDataProcessor
     * @param SourceCoordinatesDataProcessor $sourceCoordinatesDataProcessor
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        SourceRegionDataProcessor $sourceRegionDataProcessor,
        SourceCoordinatesDataProcessor $sourceCoordinatesDataProcessor
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sourceRegionDataProcessor = $sourceRegionDataProcessor;
        $this->sourceCoordinatesDataProcessor = $sourceCoordinatesDataProcessor;
    }

    /**
     * @param SourceInterface $source
     * @param array $data
     *
     * @return SourceInterface
     */
    public function hydrate(SourceInterface $source, array $data): SourceInterface
    {
        $data['general'] = $this->sourceRegionDataProcessor->execute($data['general']);
        $data['general'] = $this->sourceCoordinatesDataProcessor->execute($data['general']);

        $this->dataObjectHelper->populateWithArray($source, $data['general'], SourceInterface::class);

        return $source;
    }
}
