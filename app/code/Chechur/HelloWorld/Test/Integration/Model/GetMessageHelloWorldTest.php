<?php

declare(strict_types=1);

namespace Chechur\HelloWorld\Test\Integration\Model;

use Chechur\HelloWorldApi\Api\GetMessageHelloWorldInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test cases related to check that message was returned correctly.
 *
 * @see \Chechur\HelloWorldApi\Api\GetMessageHelloWorldInterface::execute
 */
class GetMessageHelloWorldTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GetMessageHelloWorldInterface
     */
    private $getMessageHelloWorld;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getMessageHelloWorld = $this->objectManager->get(GetMessageHelloWorldInterface::class);
        parent::setUp();
    }

    /**
     * Assert returned message.
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->assertEquals('<h1>__prefix__Hello World__suffix</h1>', $this->getMessageHelloWorld->execute());
    }
}
