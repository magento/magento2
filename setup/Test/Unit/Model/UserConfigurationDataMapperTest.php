<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\UserConfigurationDataMapper;

use Magento\Backend\Model\Url;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\Currency;
use Magento\Setup\Module\Setup;
use Magento\Store\Model\Store;

class UserConfigurationDataMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $data
     * @param array $expected
     * @dataProvider getConfigDataDataProvider
     */
    public function testGetConfigData(array $data, array $expected)
    {
        $userConfigurationDataMapper = new UserConfigurationDataMapper();
        $this->assertEquals($expected, $userConfigurationDataMapper->getConfigData($data));
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getConfigDataDataProvider()
    {
        return [
            'valid' =>
            [
                [
                    UserConfigurationDataMapper::KEY_ADMIN_USE_SECURITY_KEY => '1',
                    UserConfigurationDataMapper::KEY_BASE_URL => 'http://127.0.0.1/',
                    UserConfigurationDataMapper::KEY_BASE_URL_SECURE => 'https://127.0.0.1/',
                    UserConfigurationDataMapper::KEY_CURRENCY => 'USD',
                    UserConfigurationDataMapper::KEY_IS_SECURE => '1',
                    UserConfigurationDataMapper::KEY_IS_SECURE_ADMIN => '1',
                    UserConfigurationDataMapper::KEY_LANGUAGE => 'en_US',
                    UserConfigurationDataMapper::KEY_TIMEZONE => 'America/Chicago',
                    UserConfigurationDataMapper::KEY_USE_SEF_URL => '1',
                ],
                [
                    Store::XML_PATH_USE_REWRITES => '1',
                    Store::XML_PATH_UNSECURE_BASE_URL => 'http://127.0.0.1/',
                    Store::XML_PATH_SECURE_IN_FRONTEND => '1',
                    Store::XML_PATH_SECURE_BASE_URL => 'https://127.0.0.1/',
                    Store::XML_PATH_SECURE_IN_ADMINHTML => '1',
                    Data::XML_PATH_DEFAULT_LOCALE => 'en_US',
                    Data::XML_PATH_DEFAULT_TIMEZONE => 'America/Chicago',
                    Currency::XML_PATH_CURRENCY_BASE => 'USD',
                    Currency::XML_PATH_CURRENCY_DEFAULT => 'USD',
                    Currency::XML_PATH_CURRENCY_ALLOW => 'USD',
                    Url::XML_PATH_USE_SECURE_KEY => '1',
                ],
            ],
            'valid alphabet url' => [
                [
                    UserConfigurationDataMapper::KEY_ADMIN_USE_SECURITY_KEY => '1',
                    UserConfigurationDataMapper::KEY_BASE_URL => 'http://example.com/',
                    UserConfigurationDataMapper::KEY_BASE_URL_SECURE => 'https://example.com/',
                    UserConfigurationDataMapper::KEY_CURRENCY => 'USD',
                    UserConfigurationDataMapper::KEY_IS_SECURE => '1',
                    UserConfigurationDataMapper::KEY_IS_SECURE_ADMIN => '1',
                    UserConfigurationDataMapper::KEY_LANGUAGE => 'en_US',
                    UserConfigurationDataMapper::KEY_TIMEZONE => 'America/Chicago',
                    UserConfigurationDataMapper::KEY_USE_SEF_URL => '1',
                ],
                [
                    Store::XML_PATH_USE_REWRITES => '1',
                    Store::XML_PATH_UNSECURE_BASE_URL => 'http://example.com/',
                    Store::XML_PATH_SECURE_IN_FRONTEND => '1',
                    Store::XML_PATH_SECURE_BASE_URL => 'https://example.com/',
                    Store::XML_PATH_SECURE_IN_ADMINHTML => '1',
                    Data::XML_PATH_DEFAULT_LOCALE => 'en_US',
                    Data::XML_PATH_DEFAULT_TIMEZONE => 'America/Chicago',
                    Currency::XML_PATH_CURRENCY_BASE => 'USD',
                    Currency::XML_PATH_CURRENCY_DEFAULT => 'USD',
                    Currency::XML_PATH_CURRENCY_ALLOW => 'USD',
                    Url::XML_PATH_USE_SECURE_KEY => '1',
                ],
            ],
            'no trailing slash' =>
            [
                [
                    UserConfigurationDataMapper::KEY_ADMIN_USE_SECURITY_KEY => '1',
                    UserConfigurationDataMapper::KEY_BASE_URL => 'http://127.0.0.1',
                    UserConfigurationDataMapper::KEY_BASE_URL_SECURE => 'https://127.0.0.1',
                    UserConfigurationDataMapper::KEY_CURRENCY => 'USD',
                    UserConfigurationDataMapper::KEY_IS_SECURE => '1',
                    UserConfigurationDataMapper::KEY_IS_SECURE_ADMIN => '1',
                    UserConfigurationDataMapper::KEY_LANGUAGE => 'en_US',
                    UserConfigurationDataMapper::KEY_TIMEZONE => 'America/Chicago',
                    UserConfigurationDataMapper::KEY_USE_SEF_URL => '1',
                ],
                [
                    Store::XML_PATH_USE_REWRITES => '1',
                    Store::XML_PATH_UNSECURE_BASE_URL => 'http://127.0.0.1/',
                    Store::XML_PATH_SECURE_IN_FRONTEND => '1',
                    Store::XML_PATH_SECURE_BASE_URL => 'https://127.0.0.1/',
                    Store::XML_PATH_SECURE_IN_ADMINHTML => '1',
                    Data::XML_PATH_DEFAULT_LOCALE => 'en_US',
                    Data::XML_PATH_DEFAULT_TIMEZONE => 'America/Chicago',
                    Currency::XML_PATH_CURRENCY_BASE => 'USD',
                    Currency::XML_PATH_CURRENCY_DEFAULT => 'USD',
                    Currency::XML_PATH_CURRENCY_ALLOW => 'USD',
                    Url::XML_PATH_USE_SECURE_KEY => '1',
                ],
            ],
            'no trailing slash, alphabet url' =>
                [
                    [
                        UserConfigurationDataMapper::KEY_ADMIN_USE_SECURITY_KEY => '1',
                        UserConfigurationDataMapper::KEY_BASE_URL => 'http://example.com',
                        UserConfigurationDataMapper::KEY_BASE_URL_SECURE => 'https://example.com',
                        UserConfigurationDataMapper::KEY_CURRENCY => 'USD',
                        UserConfigurationDataMapper::KEY_IS_SECURE => '1',
                        UserConfigurationDataMapper::KEY_IS_SECURE_ADMIN => '1',
                        UserConfigurationDataMapper::KEY_LANGUAGE => 'en_US',
                        UserConfigurationDataMapper::KEY_TIMEZONE => 'America/Chicago',
                        UserConfigurationDataMapper::KEY_USE_SEF_URL => '1',
                    ],
                    [
                        Store::XML_PATH_USE_REWRITES => '1',
                        Store::XML_PATH_UNSECURE_BASE_URL => 'http://example.com/',
                        Store::XML_PATH_SECURE_IN_FRONTEND => '1',
                        Store::XML_PATH_SECURE_BASE_URL => 'https://example.com/',
                        Store::XML_PATH_SECURE_IN_ADMINHTML => '1',
                        Data::XML_PATH_DEFAULT_LOCALE => 'en_US',
                        Data::XML_PATH_DEFAULT_TIMEZONE => 'America/Chicago',
                        Currency::XML_PATH_CURRENCY_BASE => 'USD',
                        Currency::XML_PATH_CURRENCY_DEFAULT => 'USD',
                        Currency::XML_PATH_CURRENCY_ALLOW => 'USD',
                        Url::XML_PATH_USE_SECURE_KEY => '1',
                    ],
                ],
            'is_secure, is_secure_admin set but no secure base url' =>
            [
                [
                    UserConfigurationDataMapper::KEY_BASE_URL => 'http://127.0.0.1/',
                    UserConfigurationDataMapper::KEY_IS_SECURE => '1',
                    UserConfigurationDataMapper::KEY_IS_SECURE_ADMIN => '1',
                ],
                [
                    Store::XML_PATH_UNSECURE_BASE_URL => 'http://127.0.0.1/',
                ],
            ],
            'secure base url set but is_secure and is_secure_admin set to 0' =>
            [
                [
                    UserConfigurationDataMapper::KEY_BASE_URL => 'http://127.0.0.1/',
                    UserConfigurationDataMapper::KEY_BASE_URL_SECURE => 'https://127.0.0.1',
                    UserConfigurationDataMapper::KEY_IS_SECURE => '0',
                    UserConfigurationDataMapper::KEY_IS_SECURE_ADMIN => '0',
                ],
                [
                    Store::XML_PATH_UNSECURE_BASE_URL => 'http://127.0.0.1/',
                ],
            ],
            'secure base url set but is_secure and is_secure_admin not set' =>
            [
                [
                    UserConfigurationDataMapper::KEY_BASE_URL => 'http://127.0.0.1/',
                    UserConfigurationDataMapper::KEY_BASE_URL_SECURE => 'https://127.0.0.1',
                ],
                [
                    Store::XML_PATH_UNSECURE_BASE_URL => 'http://127.0.0.1/',
                ],
            ],
            'secure base url set, is_secure set to 0, is_secure_admin set to 1' =>
            [
                [
                    UserConfigurationDataMapper::KEY_BASE_URL => 'http://127.0.0.1/',
                    UserConfigurationDataMapper::KEY_BASE_URL_SECURE => 'https://127.0.0.1',
                    UserConfigurationDataMapper::KEY_IS_SECURE => '0',
                    UserConfigurationDataMapper::KEY_IS_SECURE_ADMIN => '1',
                ],
                [
                    Store::XML_PATH_UNSECURE_BASE_URL => 'http://127.0.0.1/',
                    Store::XML_PATH_SECURE_IN_FRONTEND => '0',
                    Store::XML_PATH_SECURE_BASE_URL => 'https://127.0.0.1/',
                    Store::XML_PATH_SECURE_IN_ADMINHTML => '1',
                ],
            ],
            'secure base url set, is_secure set to 1, is_secure_admin set to 0' =>
            [
                [
                    UserConfigurationDataMapper::KEY_BASE_URL => 'http://127.0.0.1/',
                    UserConfigurationDataMapper::KEY_BASE_URL_SECURE => 'https://127.0.0.1',
                    UserConfigurationDataMapper::KEY_IS_SECURE => '1',
                    UserConfigurationDataMapper::KEY_IS_SECURE_ADMIN => '0',
                ],
                [
                    Store::XML_PATH_UNSECURE_BASE_URL => 'http://127.0.0.1/',
                    Store::XML_PATH_SECURE_IN_FRONTEND => '1',
                    Store::XML_PATH_SECURE_BASE_URL => 'https://127.0.0.1/',
                    Store::XML_PATH_SECURE_IN_ADMINHTML => '0',
                ],
            ],
            'secure base url set, is_secure not set, is_secure_admin set to 1' =>
            [
                [
                    UserConfigurationDataMapper::KEY_BASE_URL => 'http://127.0.0.1/',
                    UserConfigurationDataMapper::KEY_BASE_URL_SECURE => 'https://127.0.0.1',
                    UserConfigurationDataMapper::KEY_IS_SECURE_ADMIN => '1',
                ],
                [
                    Store::XML_PATH_UNSECURE_BASE_URL => 'http://127.0.0.1/',
                    Store::XML_PATH_SECURE_BASE_URL => 'https://127.0.0.1/',
                    Store::XML_PATH_SECURE_IN_ADMINHTML => '1',
                ],
            ],
            'secure base url set, is_secure set to 1, is_secure_admin not set' =>
            [
                [
                    UserConfigurationDataMapper::KEY_BASE_URL => 'http://127.0.0.1/',
                    UserConfigurationDataMapper::KEY_BASE_URL_SECURE => 'https://127.0.0.1',
                    UserConfigurationDataMapper::KEY_IS_SECURE => '1',
                ],
                [
                    Store::XML_PATH_UNSECURE_BASE_URL => 'http://127.0.0.1/',
                    Store::XML_PATH_SECURE_IN_FRONTEND => '1',
                    Store::XML_PATH_SECURE_BASE_URL => 'https://127.0.0.1/',
                ],
            ],
        ];
    }
}
