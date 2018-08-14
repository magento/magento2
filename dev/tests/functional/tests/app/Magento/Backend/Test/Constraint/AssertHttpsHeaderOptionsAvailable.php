<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;
use Magento\Config\Test\Fixture\ConfigData;

/**
 * Assert that https header options are available.
 */
class AssertHttpsHeaderOptionsAvailable extends AbstractConstraint
{
    /**
     * Assert that https header options are available.
     *
     * @param SystemConfigEdit $systemConfigEdit
     * @param ConfigData $hsts
     * @param ConfigData $upgradeInsecure
     * @return void
     */
    public function processAssert(
        SystemConfigEdit $systemConfigEdit,
        ConfigData $hsts,
        ConfigData $upgradeInsecure
    ) {
        $this->verifyConfiguration($systemConfigEdit, $hsts);
        $this->verifyConfiguration($systemConfigEdit, $upgradeInsecure);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'HTTPS headers configuration verification successfully.';
    }

    /**
     * Verify configurations.
     *
     * @param SystemConfigEdit $systemConfigEdit
     * @param ConfigData $config
     * @return void
     */
    private function verifyConfiguration(SystemConfigEdit $systemConfigEdit, ConfigData $config)
    {
        $section = $config->getSection();
        $keys = array_keys($section);
        foreach ($keys as $key) {
            $parts = explode('/', $key, 3);
            $tabName = $parts[0];
            $groupName = $parts[1];
            $fieldName = $parts[2];
            try {
                $group = $systemConfigEdit->getForm()->getGroup($tabName, $groupName);
                $group->setValue($tabName, $groupName, $fieldName, 'Yes');
                $group->setValue($tabName, $groupName, $fieldName, 'No');
                \PHPUnit_Framework_Assert::assertTrue(
                    true,
                    $fieldName . " configuration is enabled with options Yes & No."
                );
            } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
                \PHPUnit_Framework_Assert::assertFalse(
                    true,
                    $fieldName . " configuration is not enabled with options Yes & No."
                );
            }
        }
    }
}
