<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Fixture;

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\ResourceModel\Role as RoleResource;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Creating a new admin role
 */
class Role implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'role_name' => 'Role Name %uniqid%',
        'role_type' => Group::ROLE_TYPE,
        'user_id' => 0,
        'user_type' => UserContextInterface::USER_TYPE_ADMIN,
        'pid' => 0,
        'gws_is_all' => 1,
        'gws_websites' => null,
        'gws_store_groups' => null,
        'resources' => self::RESOURCES
    ];

    private const RESOURCES = [
        'Magento_Backend::all'
    ];

    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var RoleResource
     */
    private $roleResourceModel;

    /**
     * @var RulesFactory
     */
    private $rulesFactory;

    /**
     * @var DataMerger
     */
    private $dataMerger;

    /**
     * @param RoleFactory $roleFactory
     * @param RoleResource $roleResourceModel
     * @param RulesFactory $rulesFactory
     * @param ProcessorInterface $dataProcessor
     * @param DataMerger $dataMerger
     */
    public function __construct(
        RoleFactory        $roleFactory,
        RoleResource       $roleResourceModel,
        RulesFactory       $rulesFactory,
        ProcessorInterface $dataProcessor,
        DataMerger         $dataMerger
    ) {
        $this->roleFactory = $roleFactory;
        $this->roleResourceModel = $roleResourceModel;
        $this->rulesFactory = $rulesFactory;
        $this->dataProcessor = $dataProcessor;
        $this->dataMerger = $dataMerger;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->prepareData($data);

        $websites = $this->convertGwsWebsiteStoreGroups($data['gws_websites']);
        $storeGroups = $this->convertGwsWebsiteStoreGroups($data['gws_store_groups']);

        $role = $this->roleFactory->create();
        $role->setRoleName($data['role_name'])
            ->setRoleType($data['role_type'])
            ->setPid($data['pid'])
            ->setUserType($data['user_type'])
            ->setGwsIsAll($data['gws_is_all'])
            ->setGwsWebsites($websites)
            ->setGwsStoreGroups($storeGroups);

        $result = $role->save();

        $this->rulesFactory->create()
            ->setRoleId($result['role_id'])
            ->setResources($data['resources'] ?? self::RESOURCES)
            ->saveRel();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $role = $this->roleFactory->create();
        $role->load($data->getId());

        if ($role->getId() !== null) {
            $role->delete();
        }
    }

    /**
     * Prepare admin role data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data);
        return $this->dataProcessor->process($this, $data);
    }

    /**
     * Convert GWS websites and store groups to string
     *
     * @param $data
     * @return string|null
     */
    private function convertGwsWebsiteStoreGroups($data): ?string
    {
        if (isset($data)) {
            if (is_array($data)) {
                return implode(',', $data);
            }
            if (is_string($data)) {
                return $data;
            }
        }
        return null;
    }
}
