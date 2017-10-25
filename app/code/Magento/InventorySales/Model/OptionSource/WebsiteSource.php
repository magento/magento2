<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventorySales\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Provide option values for UI
 *
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
    )
    {
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $websiteData = [];
        foreach ($this->websiteRepository->getList() as $website) {
            if ($website->getCode() === WebsiteInterface::ADMIN_CODE) {
              continue;
            }
            $websiteData[] = [
                'value' => $website->getCode(),
                'label' => $website->getName()
            ];
        }

        return $websiteData;
    }

}
