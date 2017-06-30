<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml\Config;

use Magento\Analytics\ReportXml\Config\SchemaLocator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Config\Dom\UrnResolver;

/**
 * Class SchemaLocatorTest
 */
class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var urnResolver |\PHPUnit_Framework_MockObject_MockObject
     */
    private $urnResolverMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var SchemaLocator
     */
    private $schemaLocator;

    /**
     * @var string
     */
    private $examplePath = 'urn:magento:module:Magento_Example:etc/example.xsd';

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->urnResolverMock = $this->getMockBuilder(UrnResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->schemaLocator = $this->objectManagerHelper->getObject(
            SchemaLocator::class,
            [
                'urnResolver' => $this->urnResolverMock,
                'realPath' => $this->examplePath,
            ]
        );
    }

    public function testGetSchema()
    {
        $schema = 'schema';

        $this->urnResolverMock
             ->expects($this->once())
             ->method('getRealPath')
             ->with($this->examplePath)
             ->willReturn($schema);

        $this->assertSame($schema, $this->schemaLocator->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertNull($this->schemaLocator->getPerFileSchema());
    }
}
