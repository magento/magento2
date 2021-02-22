<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures\Quote;

/**
 * Test for Magento\Setup\Fixtures\Quote\QuoteGeneratorFactory class.
 */
class QuoteGeneratorFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Setup\Fixtures\Quote\QuoteGeneratorFactory
     */
    private $fixture;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->fixture = $objectManager->getObject(
            \Magento\Setup\Fixtures\Quote\QuoteGeneratorFactory::class,
            [
                'objectManager' => $this->objectManager,
                'instanceName' => \Magento\Setup\Fixtures\Quote\QuoteGenerator::class,
            ]
        );
    }

    /**
     * Test create method.
     *
     * @return void
     */
    public function testCreate()
    {
        $result =  $this->getMockBuilder(\Magento\Setup\Fixtures\Quote\QuoteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Setup\Fixtures\Quote\QuoteGenerator::class, [])
            ->willReturn($result);

        $this->assertSame($result, $this->fixture->create([]));
    }
}
