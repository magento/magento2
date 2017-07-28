<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Source;

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
     * @var SourceCarrierHydrator
     */
    private $sourceCarrierHydrator;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param SourceCarrierHydrator $sourceCarrierHydrator
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        SourceCarrierHydrator $sourceCarrierHydrator
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sourceCarrierHydrator = $sourceCarrierHydrator;
    }

    /**
     * @param SourceInterface $source
     * @param array $data
     * @return SourceInterface
     */
    public function hydrate(SourceInterface $source, array $data)
    {
        $this->dataObjectHelper->populateWithArray($source, $data['general'], SourceInterface::class);
        $source = $this->sourceCarrierHydrator->hydrate($source, $data['general']);
        return $source;
    }
}
