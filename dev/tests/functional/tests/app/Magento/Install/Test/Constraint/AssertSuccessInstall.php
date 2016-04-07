<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Constraint;

use Magento\User\Test\Fixture\User;
use Magento\Install\Test\Page\Install;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Install\Test\Fixture\Install as InstallConfig;

/**
 * Check that Magento successfully installed.
 */
class AssertSuccessInstall extends AbstractConstraint
{
    /**
     * Admin info fields mapping.
     *
     * @var array
     */
    protected $adminFieldsList = [
        ['pageData' => 'username', 'fixture' => 'username'],
        ['pageData' => 'email', 'fixture' => 'email'],
        ['pageData' => 'your_store_address', 'fixture' => 'baseUrl'],
        ['pageData' => 'magento_admin_address', 'fixture' => 'admin']
    ];

    /**
     * Database info fields mapping.
     *
     * @var array
     */
    protected $dbFieldsList = [
        ['pageData' => 'database_name', 'fixture' => 'dbName'],
        ['pageData' => 'username', 'fixture' => 'dbUser']
    ];

    /**
     * Assert that Magento successfully installed.
     *
     * @param InstallConfig $installConfig
     * @param User $user
     * @param Install $installPage
     * @return void
     */
    public function processAssert(Install $installPage, InstallConfig $installConfig, User $user)
    {
        //TODO Nginx server does't make redirect after installation (random fail)
        sleep(5);
        if ($installPage->getInstallBlock()->isInstallationCompleted()) {
            return;
        }
        $adminData = $installPage->getInstallBlock()->getAdminInfo();
        $dbData = $installPage->getInstallBlock()->getDbInfo();

        $allData = array_merge($user->getData(), $installConfig->getData());

        foreach ($installConfig->getData() as $key => $value) {
            $allData[$key] = isset($value['value']) ? $value['value'] : $value;
        }

        $allData['baseUrl'] = (isset($allData['https']) ? $allData['https'] : $allData['baseUrl']);
        $allData['admin'] = $allData['baseUrl'] . $allData['admin'] . '/';

        $this->checkInstallData($allData, $adminData, $dbData);
    }

    /**
     * Check data on success installation page.
     *
     * @param array $allData
     * @param array $adminData
     * @param array $dbData
     * @return void
     */
    private function checkInstallData(array $allData, array $adminData, array $dbData)
    {
        foreach ($this->adminFieldsList as $field) {
            \PHPUnit_Framework_Assert::assertEquals(
                $allData[$field['fixture']],
                $adminData[$field['pageData']],
                'Wrong admin information is displayed.'
            );
        }
        foreach ($this->dbFieldsList as $field) {
            \PHPUnit_Framework_Assert::assertEquals(
                $allData[$field['fixture']],
                $dbData[$field['pageData']],
                'Wrong database information is displayed.'
            );
        }
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Install successfully finished.";
    }
}
