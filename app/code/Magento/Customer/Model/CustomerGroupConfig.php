<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

/**
 * System configuration operations for customer groups.
 * @since 2.2.0
 */
class CustomerGroupConfig implements \Magento\Customer\Api\CustomerGroupConfigInterface
{
    /**
     * @var \Magento\Config\Model\Config
     * @since 2.2.0
     */
    private $config;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     * @since 2.2.0
     */
    private $groupRepository;

    /**
     * @param \Magento\Config\Model\Config $config
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Config\Model\Config $config,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->config = $config;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setDefaultCustomerGroup($id)
    {
        if ($this->groupRepository->getById($id)) {
            $this->config->setDataByPath(
                \Magento\Customer\Model\GroupManagement::XML_PATH_DEFAULT_ID,
                $id
            );
            $this->config->save();
        }

        return $id;
    }
}
