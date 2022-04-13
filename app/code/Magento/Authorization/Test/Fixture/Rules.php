<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\SharedCatalog\Model\SharedCatalogFactory;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\TestFramework\Fixture\DataFixtureInterface;
use Magento\User\Model\UserFactory;

/**
 * Creating a new admin rules for a new role
 */
class Rules implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'id' => null,
        'role_id' => null,
        'resources' => ['Magento_Backend::all']
    ];

    /**
     * @var RulesFactory
     */
    private $rulesFactory;

    /**
     * @param RulesFactory $rulesFactory
     */
    public function __construct(
        RulesFactory $rulesFactory
    ) {
        $this->rulesFactory = $rulesFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $rules = $this->rulesFactory->create();
        $rules->setRoleId($data['role_id'] ?? null);
        $rules->setResources($data['resources'] ?? []);
        $rules->saveRel();

        return $rules;
    }
}
