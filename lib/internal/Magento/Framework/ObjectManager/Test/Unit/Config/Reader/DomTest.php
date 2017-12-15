<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Config\Reader;

require_once __DIR__ . '/_files/ConfigDomMock.php';

class DomTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $schemaLocatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validationStateMock;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Reader\Dom
     */
    protected $model;

    protected function setUp()
    {
        $this->fileResolverMock = $this->createMock(\Magento\Framework\Config\FileResolverInterface::class);
        $this->converterMock = $this->createMock(\Magento\Framework\ObjectManager\Config\Mapper\Dom::class);
        $this->schemaLocatorMock = $this->createMock(\Magento\Framework\ObjectManager\Config\SchemaLocator::class);
        $this->validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);

        $this->model = new \Magento\Framework\ObjectManager\Config\Reader\Dom(
            $this->fileResolverMock,
            $this->converterMock,
            $this->schemaLocatorMock,
            $this->validationStateMock,
            'filename.xml',
            [],
            '\ConfigDomMock'
        );
    }

    /**
     * @covers \Magento\Framework\ObjectManager\Config\Reader\Dom::_createConfigMerger()
     */
    public function testRead()
    {
        $fileList = ['first content item'];
        $this->fileResolverMock->expects($this->once())->method('get')->will($this->returnValue($fileList));
        $this->converterMock->expects($this->once())->method('convert')->with('reader dom result');
        $this->model->read();
    }
}
