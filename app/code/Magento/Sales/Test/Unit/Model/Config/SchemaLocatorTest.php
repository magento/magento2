<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Config;

use Magento\Framework\Module\Dir\Reader;
use Magento\Sales\Model\Config\SchemaLocator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_moduleReaderMock;

    /**
     * @var SchemaLocator
     */
    protected $_locator;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $this->_moduleReaderMock = $this->getMockBuilder(
            Reader::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_moduleReaderMock->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Sales'
        )->willReturn(
            'schema_dir'
        );
        $this->_locator = new SchemaLocator($this->_moduleReaderMock);
    }

    /**
     * Testing that schema has file
     */
    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/sales.xsd', $this->_locator->getSchema());
    }
}
