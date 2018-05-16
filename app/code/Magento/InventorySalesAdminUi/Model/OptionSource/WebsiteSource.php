<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesAdminUi\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * @api
 */
class WebsiteSource implements OptionSourceInterface
{
    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $websites = [];
        foreach ($this->websiteRepository->getList() as $website) {
            if ($website->getCode() === WebsiteInterface::ADMIN_CODE) {
                continue;
            }
            $websites[] = [
                'value' => $website->getCode(),
                'label' => $website->getName(),
            ];
        }
        return $websites;
    }
}
