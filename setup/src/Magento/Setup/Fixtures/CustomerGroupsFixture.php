<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Fixture for creating a Customer Groups
 * @since 2.2.0
 */
class CustomerGroupsFixture extends Fixture
{
    const DEFAULT_TAX_CLASS_ID = 3;

    /**
     * @var int
     * @since 2.2.0
     */
    protected $priority = 60;

    /**
     * @var CollectionFactory
     * @since 2.2.0
     */
    private $groupCollectionFactory;

    /**
     * @var GroupRepositoryInterface
     * @since 2.2.0
     */
    private $groupRepository;

    /**
     * @var GroupInterfaceFactory
     * @since 2.2.0
     */
    private $groupFactory;

    /**
     * @inheritDoc
     * @since 2.2.0
     */
    public function __construct(
        FixtureModel $fixtureModel,
        CollectionFactory $groupCollectionFactory,
        GroupRepositoryInterface $groupRepository,
        GroupInterfaceFactory $groupFactory
    ) {
        parent::__construct($fixtureModel);
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->groupRepository = $groupRepository;
        $this->groupFactory = $groupFactory;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function execute()
    {
        $existingCustomerGroupsCount = $this->groupCollectionFactory->create()->getSize();
        $customerGroupsCount = $this->fixtureModel->getValue('customer_groups', 0);
        if ($customerGroupsCount < 1) {
            return;
        }

        for ($i = $existingCustomerGroupsCount; $i <  $customerGroupsCount; ++$i) {
            $groupDataObject = $this->groupFactory->create();
            $groupDataObject
                ->setCode('customer_group_' . $i)
                ->setTaxClassId(self::DEFAULT_TAX_CLASS_ID);
            $this->groupRepository->save($groupDataObject);
        }
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getActionTitle()
    {
        return 'Generating customer groups';
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function introduceParamLabels()
    {
        return [
            'customer_groups' => 'Customer groups'
        ];
    }
}
