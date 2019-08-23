<?php

namespace Magento\CatalogGraphQl\Model\Category;

class GetRootCategoryId
{

    /**
     * @var int
     */
    private $rootCategoryId = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * GetRootCategoryId constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Get Root Category Id
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if ($this->rootCategoryId == null) {
            $this->rootCategoryId = (int)$this->storeManager->getStore()->getRootCategoryId();
        }

        return $this->rootCategoryId;
    }
}
