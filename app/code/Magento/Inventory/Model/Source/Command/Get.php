<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Source\Command;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;

/**
 * @inheritdoc
 */
class Get implements GetInterface
{
    /**
     * @var SourceResourceModel
     */
    private $sourceResource;

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @param SourceResourceModel $sourceResource
     * @param SourceInterfaceFactory $sourceFactory
     */
    public function __construct(
        SourceResourceModel $sourceResource,
        SourceInterfaceFactory $sourceFactory
    ) {
        $this->sourceResource = $sourceResource;
        $this->sourceFactory = $sourceFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sourceCode): SourceInterface
    {
        /** @var SourceInterface $source */
        $source = $this->sourceFactory->create();
        $this->sourceResource->load($source, $sourceCode, SourceInterface::SOURCE_CODE);

        if (null === $source->getSourceCode()) {
            throw new NoSuchEntityException(__('Source with code "%value" does not exist.', ['value' => $sourceCode]));
        }
        return $source;
    }
}
