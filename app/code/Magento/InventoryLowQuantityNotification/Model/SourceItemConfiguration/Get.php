<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\GetSourceItemConfigurationInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class Get implements GetSourceItemConfigurationInterface
{
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
     * @var GetConfiguration
     */
    private $getConfiguration;

    /**
     * @param null $getDataResourceModel @deprecated
     * @param null $getDefaultValues @deprecated
     * @param null $sourceItemConfigurationFactory @deprecated
     * @param null $dataObjectHelper @deprecated
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param SourceRepositoryInterface $sourceRepository
     * @param GetConfiguration $getConfiguration
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        $getDataResourceModel,
        $getDefaultValues,
        $sourceItemConfigurationFactory,
        $dataObjectHelper,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository = null,
        SourceRepositoryInterface $sourceRepository = null,
        GetConfiguration $getConfiguration = null
    ) {
        $this->logger = $logger;
        $this->productRepository = $productRepository ??
            ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $this->sourceRepository = $sourceRepository ??
            ObjectManager::getInstance()->get(SourceRepositoryInterface::class);
        $this->getConfiguration = $getConfiguration ?? ObjectManager::getInstance()->get(GetConfiguration::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sourceCode, string $sku): SourceItemConfigurationInterface
    {
        $this->validateInputData($sourceCode, $sku);

        try {
            return $this->getConfiguration->execute($sourceCode, $sku);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new LocalizedException(__('Could not load Source Item Configuration.'), $e);
        }
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
        if (empty($sourceCode) || empty($sku)) {
            throw new InputException(__('Wrong input data'));
        }

        try {
            // validate if the source exits
            $this->sourceRepository->get($sourceCode);
        } catch (LocalizedException $exception) {
            throw new InputException(__('Wrong input data'));
        }

        try {
            // validate if the product exits
            $this->productRepository->get($sku);
        } catch (LocalizedException $exception) {
            throw new InputException(__('Wrong input data'));
        }
    }
}
