<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Api\SaveImsUserAndRoleInterface;
use Magento\User\Model\User;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\Acl\Role\User as UserRoleType;
use Magento\AdminAdobeIms\Logger\AdminAdobeImsLogger;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class SaveImsUserAndRole
 * Save Adobe IMS User with Default Role i.e "Adobe Ims" & No Permissions
 */
class SaveImsUserAndRole implements SaveImsUserAndRoleInterface
{
    private const ADMIN_IMS_ROLE = 'Adobe Ims';

    /**
     * @var User
     */
    private User $user;

    /**
     * @var UserCollectionFactory
     */
    private UserCollectionFactory $userCollectionFactory;

    /**
     * @var RoleCollectionFactory
     */
    private RoleCollectionFactory $roleCollectionFactory;

    /**
     * @var AdminAdobeImsLogger
     */
    private AdminAdobeImsLogger $logger;

    /**
     * SaveImsUserAndRole constructor.
     * @param User $user
     * @param UserCollectionFactory $userCollectionFactory
     * @param RoleCollectionFactory $roleCollectionFactory
     * @param AdminAdobeImsLogger $logger
     */
    public function __construct(
        User $user,
        UserCollectionFactory $userCollectionFactory,
        RoleCollectionFactory $roleCollectionFactory,
        AdminAdobeImsLogger $logger
    ) {
        $this->user = $user;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->roleCollectionFactory = $roleCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function save(array $profile): void
    {
        $username = strtolower(strstr($profile['email'], '@', true));
        $userCollection = $this->userCollectionFactory->create()
            ->addFieldToFilter('email', ['eq' => $profile['email']])
            ->addFieldToFilter('username', ['eq' => $username]);

        if (!$userCollection->getSize()) {
            $roleId = $this->getImsDefaultRole();
            if ($roleId > 0) {
                try {
                    $this->user->setFirstname($profile['first_name'])
                        ->setLastname($profile['last_name'])
                        ->setUsername($username)
                        ->setPassword($this->generateRandomPassword())
                        ->setEmail($profile['email'])
                        ->setRoleType(UserRoleType::ROLE_TYPE)
                        ->setPrivileges("")
                        ->setAssertId(0)
                        ->setRoleId((int)$roleId)
                        ->setPermission('allow')
                        ->save();
                    unset($this->user);
                } catch (Exception $e) {
                    $this->logger->critical($e->getMessage());
                    throw new CouldNotSaveException(__('Could not save ims user.'));
                }
            }
        }
        $userCollection->clear();
    }

    /**
     * Fetch Default Role "Adobe Ims"
     *
     * @return int
     */
    private function getImsDefaultRole(): int
    {
        $roleId = 0;
        $roleCollection = $this->roleCollectionFactory->create()
            ->addFieldToFilter('role_name', ['eq' => self::ADMIN_IMS_ROLE])
            ->addFieldToSelect('role_id');

        if ($roleCollection->getSize() > 0) {
            $objRole = $roleCollection->fetchItem();
            $roleId = (int) $objRole->getId();
        }
        $roleCollection->clear();

        return $roleId;
    }

    /**
     * Generate random password string
     *
     * @return string
     */
    private function generateRandomPassword(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-.';
        $pass = [];
        $alphaLength = strlen($characters) - 1;
        for ($i = 0; $i < 100; $i++) {
            $n = random_int(0, $alphaLength);
            $pass[] = $characters[$n];
        }
        return implode($pass);
    }
}
