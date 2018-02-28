<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Group;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var TaxHelper
     */
    protected $taxHelper;

    /**
     * Initialize dependencies.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $customerGroupCollectionFactory
     * @param Registry $registry
     * @param GroupRepositoryInterface $groupRepository
     * @param TaxHelper $taxHelper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $customerGroupCollectionFactory,
        Registry $registry,
        GroupRepositoryInterface $groupRepository,
        TaxHelper $taxHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $customerGroupCollectionFactory->create();
        $this->registry = $registry;
        $this->groupRepository = $groupRepository;
        $this->taxHelper = $taxHelper;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Magento\Customer\Model\Group $customerGroup */
        foreach ($items as $customerGroup) {
            $this->loadedData[$customerGroup->getId()] = $customerGroup->getData();
        }

        return $this->loadedData;
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $meta['general']['children']['customer_group_code']['arguments']['data']['config']['notice'] = __(
            'Maximum length must be less then %1 characters.',
            GroupManagement::GROUP_CODE_MAX_LENGTH
        );
        $meta['general']['children']['customer_group_code']['arguments']['data']['config']['validation']['max_text_length'] =
            GroupManagement::GROUP_CODE_MAX_LENGTH;

        $groupId = $this->registry->registry(RegistryConstants::CURRENT_GROUP_ID);

        if ($groupId === null) {
            $meta['general']['children']['tax_class_id']['arguments']['data']['config']['default'] =
                $this->taxHelper->getDefaultCustomerTaxClass();
        } else {
            $customerGroup = $this->groupRepository->getById($groupId);

            if ($customerGroup->getId() == GroupManagement::NOT_LOGGED_IN_ID) {
                $meta['general']['children']['customer_group_code']['arguments']['data']['config']['disabled'] = true;
            }
        }

        return $meta;
    }
}
