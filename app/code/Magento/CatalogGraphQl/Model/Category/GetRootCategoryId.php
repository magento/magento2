<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class GetRootCategoryId
{
    /**
     * @var int
     */
    private $rootCategoryId;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * GetRootCategoryId constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Get Root Category Id
     * @return int
     * @throws LocalizedException
     */
    public function execute()
    {
        if ($this->rootCategoryId == null) {
            try {
                $this->rootCategoryId = (int)$this->storeManager->getStore()->getRootCategoryId();
            } catch (NoSuchEntityException $noSuchEntityException) {
                throw new LocalizedException(__("Store does not exist."));
            }
        }

        return $this->rootCategoryId;
    }
}
