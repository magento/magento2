<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\Stock\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Model\StockValidatorInterface;
use Magento\InventorySales\Model\ResourceModel\StockIdResolver;
use Magento\InventorySales\Model\SalesChannel;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\Data\WebsiteInterface;

class WebsiteAssignedToStockValidator implements StockValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var StockIdResolver
     */
    private $stockIdResolver;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StockIdResolver $stockIdResolver
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        WebsiteRepositoryInterface $websiteRepository,
        StockIdResolver $stockIdResolver
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->stockIdResolver = $stockIdResolver;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @inheritdoc
     */
    public function validate(StockInterface $stock): ValidationResult
    {
        $extensionAttributes = $stock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels() ?: [];

        $assignedWebsites = $errors = [];
        foreach ($salesChannels as $salesChannel) {
            if ($salesChannel->getType() === SalesChannel::TYPE_WEBSITE) {
                $assignedWebsites[] = $salesChannel->getCode();
            }
        }

        foreach ($this->websiteRepository->getList() as $website) {
            if ($website->getCode() === WebsiteInterface::ADMIN_CODE
            || in_array($website->getCode(), $assignedWebsites)) {
                continue;
            }

            if (null === $this->stockIdResolver->resolve(SalesChannel::TYPE_WEBSITE, $website->getCode())) {
                $errors[] = __('Website "%field" should be linked to stock.', ['field' => $website->getName()]);
            }
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
