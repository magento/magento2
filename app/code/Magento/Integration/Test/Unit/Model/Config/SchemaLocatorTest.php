<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Config;

use Magento\Integration\Model\Config\SchemaLocator;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Module\Dir\Reader|\PHPUnit\Framework\MockObject\MockObject */
    protected $moduleReader;

    /** @var string */
    protected $moduleDir;

    /** @var SchemaLocator */
    protected $schemaLocator;

    protected function setUp(): void
    {
        $this->moduleDir = 'moduleDirectory';
        $this->moduleReader = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $this->moduleReader->expects($this->any())
            ->method('getModuleDir')
            ->willReturn($this->moduleDir);
        $this->schemaLocator = new SchemaLocator($this->moduleReader);
    }

    public function testGetSchema()
    {
        $this->assertEquals($this->moduleDir . '/integration/config.xsd', $this->schemaLocator->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertNull($this->schemaLocator->getPerFileSchema());
    }
}
