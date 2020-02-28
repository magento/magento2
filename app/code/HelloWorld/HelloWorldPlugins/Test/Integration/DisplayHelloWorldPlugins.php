<?php

declare(strict_types=1);

namespace HelloWorld\HelloWorldPlugins\Test\Integration;

use HelloWorld\HelloWorldApi\Api\Data\HelloWorldManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DisplayHelloWorldPlugins extends TestCase
{
    private $getMessage;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMessage = $objectManager->get(HelloWorldManagementInterface::class);
    }

    public function testExecute()
    {
        $response = $this->getMessage->getHelloWorld();
        $actualResult = $response;
        $desiredResult = "prefix<h1>Hello World</h1>suffix";
        $this->assertEquals($desiredResult, $actualResult);
    }
}
