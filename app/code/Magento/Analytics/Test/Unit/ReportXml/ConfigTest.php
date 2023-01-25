<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\Config;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var DataInterface|MockObject
     */
    private $dataMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->dataMock = $this->getMockForAbstractClass(DataInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->config = $this->objectManagerHelper->getObject(
            Config::class,
            [
                'data' => $this->dataMock,
            ]
        );
    }

    public function testGet()
    {
        $queryName = 'query string';
        $queryResult = [ 'query' => 1 ];

        $this->dataMock
            ->expects($this->once())
            ->method('get')
            ->with($queryName)
            ->willReturn($queryResult);

        $this->assertSame($queryResult, $this->config->get($queryName));
    }
}
