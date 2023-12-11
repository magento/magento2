<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Layout\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Theme\Model\Layout\Config\SchemaLocator;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var SchemaLocator
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
         * @var UrnResolver $urnResolverMock | \PHPUnit\Framework\MockObject\MockObject
         */
        $urnResolverMock = $this->createMock(UrnResolver::class);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:View/PageLayout/etc/layouts.xsd')
            ->willReturn($this->schema);
        $this->object = new SchemaLocator($urnResolverMock);
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
