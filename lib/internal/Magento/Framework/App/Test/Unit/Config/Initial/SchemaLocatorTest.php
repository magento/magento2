<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config\Initial;

use Magento\Framework\App\Config\Initial\SchemaLocator;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $_moduleReaderMock;

    /**
     * @var SchemaLocator
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->_moduleReaderMock = $this->createMock(Reader::class);
        $this->_moduleReaderMock->expects($this->once())
            ->method('getModuleDir')
            ->with('etc', 'moduleName')
            ->willReturn('schema_dir');
        $this->_model = $this->objectManager->getObject(
            SchemaLocator::class,
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
