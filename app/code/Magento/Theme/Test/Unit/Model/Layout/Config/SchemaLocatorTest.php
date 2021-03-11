<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Layout\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Model\Layout\Config\SchemaLocator
     */
    protected $object;

    /**
     * @var string
     */
    protected $schema = 'framework_dir/Magento/Framework/View/PageLayout/etc/layouts.xsd';

    /**
     * Initialize testable object
     */
    protected function setUp(): void
    {
        /**
         * @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock | \PHPUnit\Framework\MockObject\MockObject
         */
        $urnResolverMock = $this->createMock(\Magento\Framework\Config\Dom\UrnResolver::class);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:View/PageLayout/etc/layouts.xsd')
            ->willReturn($this->schema);
        $this->object = new \Magento\Theme\Model\Layout\Config\SchemaLocator($urnResolverMock);
    }

    /**
     * Cover getSchema
     */
    public function testGetSchema()
    {
        $this->assertEquals($this->schema, $this->object->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals($this->schema, $this->object->getPerFileSchema());
    }
}
