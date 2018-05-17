<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model\RouteCustomizationConfig;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleReaderMock;

    /**
     * @var \Magento\WebapiAsync\Model\RouteCustomizationConfig\SchemaLocator
     */
    private $model;

    protected function setUp()
    {
        $this->moduleReaderMock = $this->createPartialMock(
            \Magento\Framework\Module\Dir\Reader::class,
            ['getModuleDir']
        );
        $this->moduleReaderMock->expects(
            $this->any()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_WebapiAsync'
        )->will(
            $this->returnValue('schema_dir')
        );

        $this->model = new \Magento\WebapiAsync\Model\RouteCustomizationConfig\SchemaLocator($this->moduleReaderMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/route_customization.xsd', $this->model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals(null, $this->model->getPerFileSchema());
    }
}
