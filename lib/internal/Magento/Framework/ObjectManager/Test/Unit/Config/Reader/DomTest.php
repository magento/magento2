<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Config\Reader;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\ObjectManager\Config\SchemaLocator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/_files/ConfigDomMock.php';

class DomTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $fileResolverMock;

    /**
     * @var MockObject
     */
    protected $converterMock;

    /**
     * @var MockObject
     */
    protected $schemaLocatorMock;

    /**
     * @var MockObject
     */
    protected $validationStateMock;

    /**
     * @var Dom
     */
    protected $model;

    protected function setUp(): void
    {
        $this->fileResolverMock = $this->getMockForAbstractClass(FileResolverInterface::class);
        $this->converterMock = $this->createMock(\Magento\Framework\ObjectManager\Config\Mapper\Dom::class);
        $this->schemaLocatorMock = $this->createMock(SchemaLocator::class);
        $this->validationStateMock = $this->getMockForAbstractClass(ValidationStateInterface::class);

        $this->model = new Dom(
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
        $this->fileResolverMock->expects($this->once())->method('get')->willReturn($fileList);
        $this->converterMock->expects($this->once())->method('convert')->with('reader dom result');
        $this->model->read();
    }
}
