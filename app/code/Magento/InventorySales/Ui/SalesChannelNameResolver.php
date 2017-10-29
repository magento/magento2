<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Ui;

use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Add grid column for sales channels
 */
class SalesChannelNameResolver
{
    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * SalesChannelNameResolver constructor.
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository
    )
    {
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * resolve the name by providing the code
     *
     * @param string $type
     * @param string $code
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(string $type, string $code): string
    {
        $name = $this->websiteRepository->get($code)->getName();
        return $name;
    }
}