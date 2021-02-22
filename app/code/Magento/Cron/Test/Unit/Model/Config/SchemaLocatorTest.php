<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_moduleReaderMock;

    /**
     * @var \Magento\Cron\Model\Config\SchemaLocator
     */
    protected $_locator;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $this->_moduleReaderMock = $this->getMockBuilder(
            \Magento\Framework\Module\Dir\Reader::class
        )->disableOriginalConstructor()->getMock();
        $this->_moduleReaderMock->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Cron'
        )->willReturn(
            'schema_dir'
        );
        $this->_locator = new \Magento\Cron\Model\Config\SchemaLocator($this->_moduleReaderMock);
    }

    /**
     * Testing that schema has file
     */
    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/crontab.xsd', $this->_locator->getSchema());
    }
}
