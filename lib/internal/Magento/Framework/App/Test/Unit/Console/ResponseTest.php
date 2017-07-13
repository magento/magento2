<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Console;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Console\Response
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Magento\Framework\App\Console\Response();
        $this->model->terminateOnSend(false);
    }

    public function testSendResponseDefaultBehaviour()
    {
        $this->assertEquals(0, $this->model->sendResponse());
    }

    /**
     * @dataProvider setCodeProvider
     */
    public function testSetCode($code, $expectedCode)
    {
        $this->model->setCode($code);
        $result = $this->model->sendResponse();
        $this->assertEquals($expectedCode, $result);
    }

    public static function setCodeProvider()
    {
        $largeCode = 256;
        $lowCode = 1;
        $lowestCode = -255;
        return [[$largeCode, 255], [$lowCode, $lowCode], [$lowestCode, $lowestCode]];
    }

    public function testSetBody()
    {
        $output = 'output';
        $this->expectOutputString($output);
        $this->model->setBody($output);
        $this->model->sendResponse();
    }

    public function testSetBodyNoOutput()
    {
        $this->expectOutputString('');
        $this->model->sendResponse();
    }
}
