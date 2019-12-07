<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config\Initial;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReaderMock;

    /**
     * @var \Magento\Framework\App\Config\Initial\SchemaLocator
     */
    protected $_model;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_moduleReaderMock = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $this->_moduleReaderMock->expects($this->once())
            ->method('getModuleDir')
            ->with('etc', 'moduleName')
            ->will($this->returnValue('schema_dir'));
        $this->_model = $this->objectManager->getObject(
            \Magento\Framework\App\Config\Initial\SchemaLocator::class,
            [
                'moduleReader' => $this->_moduleReaderMock,
                'moduleName' => 'moduleName',
            ]
        );
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/config.xsd', $this->_model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('schema_dir/config.xsd', $this->_model->getPerFileSchema());
    }
}
