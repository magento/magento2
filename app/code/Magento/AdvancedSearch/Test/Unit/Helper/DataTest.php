<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Helper;

use Magento\AdvancedSearch\Helper\Data;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * @covers \Magento\AdvancedSearch\Helper\Data
 */
class DataTest extends TestCase
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var EngineResolverInterface|MockObject
     */
    private $engineResolverMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->engineResolverMock = $this->getMockForAbstractClass(EngineResolverInterface::class);

        $this->engineResolverMock->expects($this->any())
            ->method('getCurrentSearchEngine')
            ->willReturn('');

        $this->objectManager = new ObjectManagerHelper($this);
        $this->helper = $this->objectManager->getObject(
            Data::class,
            [
                'context' => $this->contextMock,
                'engineResolver' => $this->engineResolverMock
            ]
        );
    }

    public function testIsClientOpenSearchV2()
    {
        $this->assertIsBool($this->helper->isClientOpenSearchV2());
    }
}
