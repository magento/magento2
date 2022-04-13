<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Fixture;

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\ResourceModel\Role as RoleResource;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\DataObject;
use Magento\SharedCatalog\Model\SharedCatalogFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\User\Model\UserFactory;

/**
 * Creating a new admin role
 */
class Role implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'role_name' => 'Role Name %uniqid%',
        'role_type' => Group::ROLE_TYPE,
        'user_id' => 0,
        'user_type' => UserContextInterface::USER_TYPE_ADMIN
    ];

    private const DEFAULT_DATA_RULES = [
        'id' => null,
        'role_id' => null,
        'resources' => ['Magento_Backend::all']
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
     * @param RoleFactory $roleFactory
     * @param RoleResource $roleResourceModel
     * @param RulesFactory $rulesFactory
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        RoleFactory        $roleFactory,
        RoleResource       $roleResourceModel,
        RulesFactory       $rulesFactory,
        ProcessorInterface $dataProcessor
    ) {
        $this->roleFactory = $roleFactory;
        $this->roleResourceModel = $roleResourceModel;
        $this->rulesFactory = $rulesFactory;
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $role = $this->roleFactory->create();
        $role->setData($this->prepareData(array_diff_key($data, self::DEFAULT_DATA_RULES)));
        $this->roleResourceModel->save($role);

        $rules = $this->rulesFactory->create();
        $rules->setRoleId($role->getId() ?? null);
        $rules->setResources($data['resources'] ?? self::DEFAULT_DATA_RULES['resources']);
        $rules->saveRel();

        return $role;
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
        $data = array_merge(self::DEFAULT_DATA, $data);
        return $this->dataProcessor->process($this, $data);
    }
}
