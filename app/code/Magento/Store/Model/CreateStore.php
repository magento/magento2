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
     *
     * @param \Magento\Store\Api\Data\GroupInterfaceFactory $groupFactory
     */
    public function __construct(
        \Magento\Store\Api\Data\GroupInterfaceFactory $groupFactory
    ) {
        $this->groupFactory = $groupFactory;
    }

    /**
     *
     * @param \Magento\Store\Api\Data\StoreInterface $storeModel
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function execute(StoreInterface $storeModel): StoreInterface
    {
        /** @var \Magento\Store\Model\Group $groupModel */
        $groupModel = $this->groupFactory->create()
            ->load($storeModel->getGroupId());

        $storeModel->setWebsiteId($groupModel->getWebsiteId());
        $storeModel->save();

        return $storeModel;
    }
}
