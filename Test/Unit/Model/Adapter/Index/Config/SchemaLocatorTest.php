<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleReaderMock;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Index\Config\SchemaLocator
     */
    protected $model;

    protected function setUp()
    {
        $this->moduleReaderMock = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $this->moduleReaderMock->expects(
            $this->any()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Elasticsearch'
        )->will(
            $this->returnValue('schema_dir')
        );

        $this->model = new \Magento\Elasticsearch\Model\Adapter\Index\Config\SchemaLocator($this->moduleReaderMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/esconfig.xsd', $this->model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('schema_dir/esconfig.xsd', $this->model->getPerFileSchema());
    }
}
