<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\GroupExcludedWebsiteRepository;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Delete customer group excluded websites while deleting customer group by id.
 */
class DeleteCustomerGroupExcludedWebsite
{
    /**
     * @var GroupExcludedWebsiteRepository
     */
    private $groupExcludedWebsiteRepository;

    /**
     * @var Processor
     */
    private $priceIndexProcessor;

    /**
     * @param GroupExcludedWebsiteRepository $groupExcludedWebsiteRepository
     * @param Processor $priceIndexProcessor
     */
    public function __construct(
        GroupExcludedWebsiteRepository $groupExcludedWebsiteRepository,
        Processor $priceIndexProcessor
    ) {
        $this->groupExcludedWebsiteRepository = $groupExcludedWebsiteRepository;
        $this->priceIndexProcessor = $priceIndexProcessor;
    }

    /**
     * Delete excluded customer group websites while deleting customer group by id.
     *
     * @param GroupRepositoryInterface $subject
     * @param bool $result
     * @param string $groupId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteById(GroupRepositoryInterface $subject, bool $result, string $groupId): bool
    {
        $excludedWebsites = $this->groupExcludedWebsiteRepository->getCustomerGroupExcludedWebsites((int)$groupId);
        if (!empty($excludedWebsites)) {
            try {
                $this->groupExcludedWebsiteRepository->delete((int)$groupId);
            } catch (LocalizedException $e) {
                throw new CouldNotDeleteException(
                    __(
                        'Could not delete customer group website with ID: %1',
                        $groupId
                    ),
                    $e
                );
            }
            // invalidate product price index if websites were deleted from customer group exclusion
            $priceIndexer = $this->priceIndexProcessor->getIndexer();
            $priceIndexer->invalidate();
        }

        return $result;
    }
}
