<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config;

use Magento\Store\Model\Config\Validator;
use Magento\Store\Model\ScopeInterface;

/**
 * Test for Validator
 *
 * @see Validator
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array $data
     * @param array $result
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $data, array $result)
    {
        $model = new Validator();

        $this->assertEquals($result, $model->validate($data));
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        $errorMessage = 'Scopes data should have at least one not admin website, group and store.';
        return [
            [
                [],
                [$errorMessage]
            ],
            [
                [
                    ScopeInterface::SCOPE_GROUPS => [],
                    ScopeInterface::SCOPE_STORES => [],
                ],
                [$errorMessage]
            ],
            [
                [
                    ScopeInterface::SCOPE_GROUPS => [0 => ['name' => 'group one']],
                    ScopeInterface::SCOPE_STORES => ['admin' => ['name' => 'admin store']],
                    ScopeInterface::SCOPE_WEBSITES => ['admin' => ['name' => 'admin website']]
                ],
                [$errorMessage]
            ],
            [
                [
                    ScopeInterface::SCOPE_GROUPS => [
                        0 => ['name' => 'group one'],
                        1 => ['name' => 'group two']
                    ],
                    ScopeInterface::SCOPE_STORES => [
                        'admin' => ['name' => 'admin store'],
                        'store-two' => ['name' => 'store two'],
                    ],
                    ScopeInterface::SCOPE_WEBSITES => [
                        'admin' => ['name' => 'admin website']
                    ]
                ],
                [$errorMessage]
            ],
            [
                [
                    ScopeInterface::SCOPE_GROUPS => [
                        0 => ['name' => 'group one'],
                        1 => ['name' => 'group two']
                    ],
                    ScopeInterface::SCOPE_STORES => [
                        'admin' => ['name' => 'admin store'],
                        'store-two' => ['name' => 'store two'],
                    ],
                    ScopeInterface::SCOPE_WEBSITES => [
                        'admin' => ['name' => 'admin website'],
                        'website-two' => ['name' => 'website two'],
                    ]
                ],
                []
            ]
        ];
    }
}
