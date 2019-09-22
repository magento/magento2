<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration\GetData as GetDataModel;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\InventoryLowQuantityNotificationApi\Api\GetSourceItemConfigurationInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class Get implements GetSourceItemConfigurationInterface
{
    /**
     * @var GetDataModel
     */
    private $getDataResourceModel;

    /**
     * @var GetDefaultValues
     */
    private $getDefaultValues;

    /**
     * @var SourceItemConfigurationInterfaceFactory
     */
    private $sourceItemConfigurationFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param GetDataModel $getDataResourceModel
     * @param GetDefaultValues $getDefaultValues
     * @param SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface  $productRepository,
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        GetDataModel $getDataResourceModel,
        GetDefaultValues $getDefaultValues,
        SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory,
        DataObjectHelper $dataObjectHelper,
        LoggerInterface $logger,
        ProductRepositoryInterface  $productRepository,
        SourceRepositoryInterface $sourceRepository
    ) {
        $this->getDataResourceModel = $getDataResourceModel;
        $this->getDefaultValues = $getDefaultValues;
        $this->sourceItemConfigurationFactory = $sourceItemConfigurationFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sourceCode, string $sku): SourceItemConfigurationInterface
    {
        $this->validateInputData($sourceCode, $sku);

        try {
            return $this->getConfiguration($sourceCode, $sku);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new LocalizedException(__('Could not load Source Item Configuration.'), $e);
        }
    }

    /**
     * Loads the low quantity notification config from the database.
     *
     * @param string $sourceCode
     * @param string $sku
     * @return SourceItemConfigurationInterface
     */
    private function getConfiguration(string $sourceCode, string $sku): SourceItemConfigurationInterface
    {
        $sourceItemConfigurationData = $this->getDataResourceModel->execute($sourceCode, $sku);

        if (null === $sourceItemConfigurationData) {
            $sourceItemConfigurationData = $this->getDefaultValues->execute($sourceCode, $sku);
        }

        /** @var SourceItemConfigurationInterface $sourceItem */
        $sourceItemConfiguration = $this->sourceItemConfigurationFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $sourceItemConfiguration,
            $sourceItemConfigurationData,
            SourceItemConfigurationInterface::class
        );
        return $sourceItemConfiguration;
    }

    /**
     * Validation for the given data to make sure that sku and source code exits in the system.
     *
     * @param string $sourceCode
     * @param string $sku
     * @throws InputException
     */
    private function validateInputData(string $sourceCode, string $sku): void
    {
        if (empty($sourceCode)) {
            throw new InputException(__('Wrong input data for sourcecode is empty.'));
        }

        if (empty($sku)) {
            throw new InputException(__('Wrong input data for sku is empty.'));
        }

        try {
            // validate if the source exits
            $this->sourceRepository->get($sourceCode);
        } catch (LocalizedException $exception) {
            throw new InputException(__('Source code %1 doesnt exits.', $sourceCode));
        }

        try {
            // validate if the product exits
            $this->productRepository->get($sku);
        } catch (LocalizedException $exception) {
            throw new InputException(__('Sku %1 doesnt exits.', $sku));
        }
    }
}
