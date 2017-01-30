<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Layout\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        /**
         * @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock | \PHPUnit_Framework_MockObject_MockObject
         */
        $urnResolverMock = $this->getMock('Magento\Framework\Config\Dom\UrnResolver', [], [], '', false);
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
