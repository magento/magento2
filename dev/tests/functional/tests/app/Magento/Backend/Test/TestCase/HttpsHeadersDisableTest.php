<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;
use Magento\Config\Test\Fixture\ConfigData;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 *
 * 1. Login to backend.
 * 2. Go to Stores -> Configuration -> General -> Web.
 * 3. Set "Use Secure URLs on Storefront" to Yes.
 * 4. Set "Use Secure URLs in Admin" to No.
 * 5. Perform asserts.
 *
 * @ZephyrId MAGETWO-46903
 */
class HttpsHeadersDisableTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Open backend system config and set configuration values.
     *
     * @param SystemConfigEdit $systemConfigEdit
     * @param ConfigData $httpsConfig
     * @return void
     */
    public function test(SystemConfigEdit $systemConfigEdit, ConfigData $httpsConfig)
    {
        $systemConfigEdit->open();
        $section = $httpsConfig->getSection();
        $keys = array_keys($section);
        foreach ($keys as $key) {
            $parts = explode('/', $key, 3);
            $tabName = $parts[0];
            $groupName = $parts[1];
            $fieldName = $parts[2];
            $systemConfigEdit->getForm()->getGroup($tabName, $groupName)
                ->setValue($tabName, $groupName, $fieldName, $section[$key]['label']);
        }
    }
}
