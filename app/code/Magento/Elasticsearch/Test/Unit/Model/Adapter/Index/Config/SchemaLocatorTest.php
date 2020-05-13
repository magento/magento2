<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index\Config;

use Magento\Elasticsearch\Model\Adapter\Index\Config\SchemaLocator;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $moduleReaderMock;

    /**
     * @var SchemaLocator
     */
    protected $model;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->moduleReaderMock = $this->createMock(Reader::class);
        $this->moduleReaderMock->expects(
            $this->any()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Elasticsearch'
        )->willReturn(
            'schema_dir'
        );

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            SchemaLocator::class,
            [
                'moduleReader' => $this->moduleReaderMock
            ]
        );
    }

    /**
     * Test getSchema() method.
     */
    public function testGetSchema()
    {
        $this->assertEquals('schema_dir' . DIRECTORY_SEPARATOR . 'esconfig.xsd', $this->model->getSchema());
    }

    /**
     * Test getPerFileSchema() method.
     */
    public function testGetPerFileSchema()
    {
        $this->assertEquals('schema_dir' . DIRECTORY_SEPARATOR . 'esconfig.xsd', $this->model->getPerFileSchema());
    }
}
