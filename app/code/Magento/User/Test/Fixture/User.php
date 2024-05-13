<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\TestFramework\Bootstrap;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User as UserResource;

/**
 * Creating a new admin user with variable role
 */
class User implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'username' => 'adminuser%uniqid%',
        'firstname' => 'AdminFirstname%uniqid%',
        'lastname' => 'AdminLastname%uniqid%',
        'email' => 'adminuser%uniqid%@example.com',
        'password' => Bootstrap::ADMIN_PASSWORD,
        'interface_locale' => 'en_US',
        'is_active' => 1
    ];

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var UserResource
     */
    private $userResource;

    /**
     * @param UserFactory $userFactory
     * @param UserResource $userResource
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        UserFactory $userFactory,
        UserResource $userResource,
        ProcessorInterface $dataProcessor
    ) {
        $this->userFactory = $userFactory;
        $this->userResource = $userResource;
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $user = $this->userFactory->create();
        $user->setData($this->prepareData($data));
        $user->setRoleId($data['role_id'] ?? 0);
        $this->userResource->save($user);

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $user = $this->userFactory->create();
        $user->load($data->getData('username'), 'username');

        if ($user->getId() !== null) {
            $this->userResource->delete($user);
        }
    }

    /**
     * Prepare admin user data
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
