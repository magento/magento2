<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Class CreateStore
 * Model for create a new store view
 */
class CreateStore
{
    /**
     * @var \Magento\Store\Api\Data\GroupInterfaceFactory
     */
    private $groupFactory;

    /**
     * @var \Magento\Store\Api\Data\StoreInterfaceFactory
     */
    private $storeFactory;

    /**
     *
     * @param \Magento\Store\Api\Data\GroupInterfaceFactory $groupFactory
     * @param \Magento\Store\Api\Data\StoreInterfaceFactory $storeFactory
     */
    public function __construct(
        \Magento\Store\Api\Data\GroupInterfaceFactory $groupFactory,
        \Magento\Store\Api\Data\StoreInterfaceFactory $storeFactory
    ) {
        $this->groupFactory = $groupFactory;
        $this->storeFactory = $storeFactory;
    }

    /**
     *
     * @param array $data
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function execute(array $data): StoreInterface
    {
        /** @var \Magento\Store\Model\Storev $storeModel */
        $storeModel = $this->storeFactory->create(['data' => $data]);

        /** @var \Magento\Store\Model\Group $groupModel */
        $groupModel = $this->groupFactory->create()
            ->load($storeModel->getGroupId());

        $storeModel->setWebsiteId($groupModel->getWebsiteId());
        $storeModel->save();

        return $storeModel;
    }
}
