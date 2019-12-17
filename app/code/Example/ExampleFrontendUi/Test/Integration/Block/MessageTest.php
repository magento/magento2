<?php
declare(strict_types=1);

namespace Example\ExampleFrontendUi\Test\Integration\Block;

use Example\ExampleFrontendUi\Block\Index;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * MassageTest
 */
class MessageTest extends TestCase
{
    /**
     * @var Index
     */
    private $testObject;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->testObject = $objectManager->get(Index::class);
    }

    /**
     * Retrive message
     */
    public function testGetMessage(): void
    {
        $expectedMessage = "Hello World!";
        $actualMessage = $this->testObject->getMessage();
        $this->assertStringMatchesFormat('Hello %a', $actualMessage, 'Does not match the format string');
        $this->assertEquals($expectedMessage, $actualMessage);
    }
}
