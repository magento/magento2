<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleReaderMock;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Index\Config\SchemaLocator
     */
    protected $model;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->moduleReaderMock = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
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
            \Magento\Elasticsearch\Model\Adapter\Index\Config\SchemaLocator::class,
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
