<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml\DB;

use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NameResolverTest extends TestCase
{
    /**
     * @var NameResolver|MockObject
     */
    private $nameResolverMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->nameResolverMock = $this->getMockBuilder(NameResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->nameResolver = $this->objectManagerHelper->getObject(NameResolver::class);
    }

    public function testGetName()
    {
        $elementConfigMock = [
            'name' => 'sales_order',
            'alias' => 'sales',
        ];

        $this->assertSame('sales_order', $this->nameResolver->getName($elementConfigMock));
    }

    /**
     * @param array $elementConfig
     * @param string|null $elementAlias
     *
     * @dataProvider getAliasDataProvider
     */
    public function testGetAlias($elementConfig, $elementAlias)
    {
        $elementName = 'elementName';

        $this->nameResolverMock
            ->expects($this->once())
            ->method('getName')
            ->with($elementConfig)
            ->willReturn($elementName);

        $this->assertSame($elementAlias ?: $elementName, $this->nameResolverMock->getAlias($elementConfig));
    }

    /**
     * @return array
     */
    public function getAliasDataProvider()
    {
        return [
            'ElementConfigWithAliases' => [
                ['alias' => 'sales', 'name' => 'sales_order'],
                'sales',
            ],
            'ElementConfigWithoutAliases' => [
                ['name' => 'sales_order'],
                null,
            ]
        ];
    }
}
