<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Store\Model\ResourceModel\Website;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventorySalesApi\Model\GetAssignedStockIdForWebsiteInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ResourceModel\Website as WebsiteResourceModel;
use Magento\Store\Model\Website;

/**
 * Assign the website to the default stock
 */
class AssignWebsiteToDefaultStockPlugin
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var GetAssignedStockIdForWebsiteInterface
     */
    private $getAssignedStockIdForWebsite;

    /**
     * @param StockRepositoryInterface $stockRepository
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param GetAssignedStockIdForWebsiteInterface $getAssignedStockIdForWebsite
     */
    public function __construct(
        StockRepositoryInterface $stockRepository,
        DefaultStockProviderInterface $defaultStockProvider,
        SalesChannelInterfaceFactory $salesChannelFactory,
        GetAssignedStockIdForWebsiteInterface $getAssignedStockIdForWebsite
    ) {
        $this->stockRepository = $stockRepository;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->getAssignedStockIdForWebsite = $getAssignedStockIdForWebsite;
    }

    /**
     * @param WebsiteResourceModel $subject
     * @param WebsiteResourceModel $result
     * @param Website|AbstractModel $website
     * @return WebsiteResourceModel
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        WebsiteResourceModel $subject,
        WebsiteResourceModel $result,
        AbstractModel $website
    ) {
        $websiteCode = $website->getCode();

        if ($websiteCode === WebsiteInterface::ADMIN_CODE) {
            return $result;
        }

        // checks is some stock already assigned to this website
        if ($this->getAssignedStockIdForWebsite->execute($websiteCode) !== null) {
            return $result;
        }

        $defaultStockId = $this->defaultStockProvider->getId();
        $defaultStock = $this->stockRepository->get($defaultStockId);

        $extensionAttributes = $defaultStock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels();
        $salesChannels[] = $this->createSalesChannelByWebsiteCode($websiteCode);

        $extensionAttributes->setSalesChannels($salesChannels);
        $this->stockRepository->save($defaultStock);

        return $result;
    }

    /**
     * Create the sales channel by given website code
     *
     * @param string $websiteCode
     * @return SalesChannelInterface
     */
    private function createSalesChannelByWebsiteCode(string $websiteCode): SalesChannelInterface
    {
        $salesChannel = $this->salesChannelFactory->create();
        $salesChannel->setCode($websiteCode);
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);
        return $salesChannel;
    }
}
