<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Validator;

use Magento\Framework\Config\ConfigOptionsListConstants as ConfigOption;
use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Model\Installer;

/**
 * Admin user credentials validator
 */
class AdminCredentialsValidator
{
    /**
     * @var \Magento\Setup\Module\ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var \Magento\Setup\Model\AdminAccountFactory
     */
    private $adminAccountFactory;

    /**
     * @var \Magento\Setup\Module\SetupFactory
     */
    private $setupFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Setup\Model\AdminAccountFactory $adminAccountFactory
     * @param \Magento\Setup\Module\ConnectionFactory $connectionFactory
     * @param \Magento\Setup\Module\SetupFactory $setupFactory
     */
    public function __construct(
        \Magento\Setup\Model\AdminAccountFactory $adminAccountFactory,
        \Magento\Setup\Module\ConnectionFactory $connectionFactory,
        \Magento\Setup\Module\SetupFactory $setupFactory
    ) {
        $this->connectionFactory = $connectionFactory;
        $this->adminAccountFactory = $adminAccountFactory;
        $this->setupFactory = $setupFactory;
    }

    /**
     * Validate admin user name and email.
     *
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function validate(array $data)
    {
        try {
            $dbConnection = $this->connectionFactory->create([
                ConfigOption::KEY_NAME => $data[ConfigOption::INPUT_KEY_DB_NAME],
                ConfigOption::KEY_HOST => $data[ConfigOption::INPUT_KEY_DB_HOST],
                ConfigOption::KEY_USER => $data[ConfigOption::INPUT_KEY_DB_USER],
                ConfigOption::KEY_PASSWORD => $data[ConfigOption::INPUT_KEY_DB_PASSWORD],
                ConfigOption::KEY_PREFIX => $data[ConfigOption::INPUT_KEY_DB_PREFIX]
            ]);

            $userName = $data[AdminAccount::KEY_USER];
            $userEmail = $data[AdminAccount::KEY_EMAIL];
            $userTable = $dbConnection->getTableName('admin_user');
            $result = $dbConnection->fetchRow(
                "SELECT user_id, username, email FROM {$userTable} WHERE username = :username OR email = :email",
                ['username' => $userName, 'email' => $userEmail]
            );
            $setup = $this->setupFactory->create();
            $adminAccount = $this->adminAccountFactory->create(
                $setup,
                [AdminAccount::KEY_USER => $userName, AdminAccount::KEY_EMAIL => $userEmail]
            );
        } catch (\Exception $e) {
            return;
        }

        if (!empty($result)) {
            $adminAccount->validateUserMatches($result['username'], $result['email']);
        }
    }
}
