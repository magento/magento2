<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Config;

use Magento\Analytics\Model\Config\SchemaLocator;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var urnResolver|\PHPUnit_Framework_MockObject_MockObject
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
    private $schema = 'urn:magento:module:Magento_Analytics:etc/test.xsd';

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
                'schema' => $this->schema,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetSchema()
    {
        $schemaRealPath = '/path/test.xml';

        $this->urnResolverMock
             ->expects($this->once())
             ->method('getRealPath')
             ->with($this->schema)
             ->willReturn($schemaRealPath);

        $this->assertSame($schemaRealPath, $this->schemaLocator->getSchema());
    }

    /**
     * @return void
     */
    public function testGetPerFileSchema()
    {
        $this->assertNull($this->schemaLocator->getPerFileSchema());
    }
}
