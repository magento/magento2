<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\ViewModel;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Wishlist\Helper\Data as HelperData;

/**
 * ViewModel for Wishlist Sidebar Block
 */
class WishlistData implements ArgumentInterface
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param HelperData $helperData
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        HelperData $helperData
    ) {
        $this->objectManager = $objectManager;
        $this->helperData = $helperData ?: $this->objectManager->get(HelperData::class);
    }

    /**
     * Retrieve customer wishlist url
     *
     * @param int $wishlistId
     * @return string
     */
    public function getListUrl($wishlistId = null): string
    {
        return $this->helperData->getListUrl($wishlistId);
    }

    /**
     * Check is allow wishlist module
     *
     * @return bool
     */
    public function isAllow(): bool
    {
        return $this->helperData->isAllow();
    }
}
