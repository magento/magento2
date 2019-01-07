<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Http\PayloadConverter;
use Magento\AuthorizenetAcceptjs\Gateway\Request\AuthenticationDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\Request\RequestTypeBuilder;

class RequestTypeBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AuthenticationDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new RequestTypeBuilder('foo');
    }

    /**
     * @covers \Magento\Braintree\Gateway\Request\CaptureDataBuilder::build
     */
    public function testBuild()
    {
        $expected = [
            PayloadConverter::PAYLOAD_TYPE => 'foo'
        ];

        $buildSubject = [];
        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
