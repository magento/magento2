<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Customer\Model\Data\GroupExcludedWebsiteFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Save customer group websites excluded for certain customer group.
 */
class SaveCustomerGroupExcludedWebsite
{
    /**
     * @var GroupExcludedWebsiteFactory
     */
    private $groupExcludedWebsiteFactory;

    /**
     * @var GroupExcludedWebsiteRepositoryInterface
     */
    private $groupExcludedWebsiteRepository;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var Processor
     */
    private $priceIndexProcessor;

    /**
     * @param GroupExcludedWebsiteFactory $groupExcludedWebsiteFactory
     * @param GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository
     * @param SystemStore $systemStore
     * @param Processor $priceIndexProcessor
     */
    public function __construct(
        GroupExcludedWebsiteFactory $groupExcludedWebsiteFactory,
        GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository,
        SystemStore $systemStore,
        Processor $priceIndexProcessor
    ) {
        $this->groupExcludedWebsiteFactory = $groupExcludedWebsiteFactory;
        $this->groupExcludedWebsiteRepository = $groupExcludedWebsiteRepository;
        $this->systemStore = $systemStore;
        $this->priceIndexProcessor = $priceIndexProcessor;
    }

    /**
     * Save excluded websites for customer group.
     *
     * @param GroupRepositoryInterface $subject
     * @param GroupInterface $result
     * @param GroupInterface $group
     * @return GroupInterface
     *
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        GroupRepositoryInterface $subject,
        GroupInterface $result,
        GroupInterface $group
    ): GroupInterface {
        if ($result->getExtensionAttributes() && $result->getExtensionAttributes()->getExcludeWebsiteIds() !== null) {
            $websitesToExclude = array_intersect(
                $this->getAllWebsites(),
                $result->getExtensionAttributes()->getExcludeWebsiteIds()
            );
            $customerGroupId = (int)$result->getId();

            // prevent NOT LOGGED IN customers with id 0 to have excluded websites
            if ($customerGroupId !== null && $customerGroupId !== 0) {
                $excludedWebsites = $this->groupExcludedWebsiteRepository
                    ->getCustomerGroupExcludedWebsites($customerGroupId);
                $isValueChanged = $this->isValueChanged($excludedWebsites, $websitesToExclude);
                if ($isValueChanged) {
                    $this->groupExcludedWebsiteRepository->delete($customerGroupId);
                    foreach ($websitesToExclude as $websiteToExclude) {
                        $groupExcludedWebsite = $this->groupExcludedWebsiteFactory->create();
                        $groupExcludedWebsite->setGroupId($customerGroupId);
                        $groupExcludedWebsite->setExcludedWebsiteId((int)$websiteToExclude);
                        try {
                            $this->groupExcludedWebsiteRepository->save($groupExcludedWebsite);
                        } catch (LocalizedException $e) {
                            throw new CouldNotSaveException(
                                __(
                                    'Could not save customer group website to exclude with ID: %1',
                                    $websiteToExclude
                                ),
                                $e
                            );
                        }
                    }
                    // invalidate product price index if new websites are excluded from customer group
                    $priceIndexer = $this->priceIndexProcessor->getIndexer();
                    $priceIndexer->invalidate();
                }
            }
        }

        return $result;
    }

    /**
     * Get all websites.
     *
     * @return array
     */
    private function getAllWebsites(): array
    {
        $websiteCollection = $this->systemStore->getWebsiteCollection();

        $websites = [];
        foreach ($websiteCollection as $website) {
            $websites[] = (int)$website->getWebsiteId();
        }

        return $websites;
    }

    /**
     * Check if there are new websites to exclude from the customer group.
     *
     * @param array $currentValues
     * @param array $newValues
     * @return bool
     */
    private function isValueChanged(array $currentValues, array $newValues): bool
    {
        return !($currentValues === array_intersect($currentValues, $newValues)
            && $newValues === array_intersect($newValues, $currentValues));
    }
}
