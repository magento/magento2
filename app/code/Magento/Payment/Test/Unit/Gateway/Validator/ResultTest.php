<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Validator;

use Magento\Framework\Phrase;
use Magento\Payment\Gateway\Validator\Result;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    /** @var Result */
    protected $model;

    /**
     * @param $isValid mixed
     * @param $failsDescription array
     * @param $expectedIsValid mixed
     * @param $expectedFailsDescription array
     * @dataProvider resultDataProvider
     */
    public function testResult($isValid, $failsDescription, $expectedIsValid, $expectedFailsDescription)
    {
        $this->model = new Result($isValid, $failsDescription);
        $this->assertEquals($expectedIsValid, $this->model->isValid());
        $this->assertEquals($expectedFailsDescription, $this->model->getFailsDescription());
    }

    protected function getMockForPhrase() {
        $phraseMock = $this->getMockBuilder(Phrase::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $phraseMock;
    }

    /**
     * @return array
     */
    public static function resultDataProvider()
    {
        $phraseMock = static fn (self $testCase) => $testCase->getMockForPhrase();
        return [
            [true, [$phraseMock, $phraseMock], true, [$phraseMock, $phraseMock]],
            ['', [], false, []],
        ];
    }
}
