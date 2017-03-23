<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Config\Integration;

use Magento\Integration\Model\Config\Integration\SchemaLocator;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject */
    protected $moduleReader;

    /** @var string */
    protected $moduleDir;

    /** @var SchemaLocator */
    protected $schemaLocator;

    protected function setUp()
    {
        $this->moduleDir = 'moduleDirectory';
        $this->moduleReader = $this->getMock(\Magento\Framework\Module\Dir\Reader::class, [], [], '', false);
        $this->moduleReader->expects($this->any())
            ->method('getModuleDir')
            ->willReturn($this->moduleDir);
        $this->schemaLocator = new SchemaLocator($this->moduleReader);
    }

    public function testGetSchema()
    {
        $this->assertEquals($this->moduleDir . '/integration/api.xsd', $this->schemaLocator->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertNull($this->schemaLocator->getPerFileSchema());
    }
}
