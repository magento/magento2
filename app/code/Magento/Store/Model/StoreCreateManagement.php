<?php

namespace Magento\Store\Model;

use Magento\Store\Api\StoreCreateManagementInterface;

class StoreCreateManagement implements StoreCreateManagementInterface
{
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    private $groupFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private $storeFactory;

    /**
     *
     * @param \Magento\Store\Model\WebsiteFactory     $websiteFactory
     * @param \Magento\Store\Model\GroupFactory       $groupFactory
     * @param \Magento\Store\Model\StoreFactory       $storeFactory
     */
    public function __construct(
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\GroupFactory $groupFactory,
        \Magento\Store\Model\StoreFactory $storeFactory
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->groupFactory = $groupFactory;
        $this->storeFactory = $storeFactory;
    }

    /**
     *
     * @param  array $data
     * @return \Magento\Store\Model\Store
     */
    public function create($data)
    {
        /** @var \Magento\Store\Model\Store $storeModel */
        $storeModel = $this->storeFactory->create();
        $storeModel->setData($data);

        /** @var \Magento\Store\Model\Group $groupModel */
        $groupModel = $this->groupFactory->create()
            ->load($storeModel->getGroupId());

        $storeModel->setWebsiteId($groupModel->getWebsiteId());
        $storeModel->save();

        return $storeModel;
    }

    /**
     *
     * @return int
     */
    public function getDefaultGroupId()
    {
        $groups = $this->getAllStoreGroups();

        return current($groups);
    }


    /**
     * Retrieve list of store groups
     *
     * @return array
     */
    private function getAllStoreGroups()
    {
        $websites = $this->websiteFactory->create()->getCollection();
        $allgroups = $this->groupFactory->create()->getCollection();
        $groups = [];
        foreach ($websites as $website) {
            foreach ($allgroups as $group) {
                if ($group->getWebsiteId() == $website->getId()) {
                    $groups[$group->getWebsiteId() . '-' . $group->getId()] = $group->getId();
                }
            }
        }

        return $groups;
    }
}
