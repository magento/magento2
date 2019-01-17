<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\PassthroughDataBuilder;
use Magento\AuthorizenetAcceptjs\Model\PassthroughDataObject;

class PassthroughDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild()
    {
        $passthroughData = new PassthroughDataObject([
            'foo' => 'bar',
            'baz' => 'bash'
        ]);
        $builder = new PassthroughDataBuilder($passthroughData);

        $expected = [
            'transactionRequest' => [
                'userFields' => [
                    'userField' => [
                        [
                            'name' => 'foo',
                            'value' => 'bar'
                        ],
                        [
                            'name' => 'baz',
                            'value' => 'bash'
                        ],
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $builder->build([]));
    }
}
