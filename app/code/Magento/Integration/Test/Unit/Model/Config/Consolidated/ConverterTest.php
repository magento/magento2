<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Config\Consolidated;

use \Magento\Integration\Model\Config\Consolidated\Converter;

/**
 * Test for conversion of integration XML config into array representation.
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $model;

    /** @var \Magento\Framework\Acl\AclResource\ProviderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceProviderMock;

    public function setUp()
    {
        $this->resourceProviderMock = $this->getMockBuilder('Magento\Framework\Acl\AclResource\ProviderInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Integration\Model\Config\Consolidated\Converter',
            [
                'resourceProvider' => $this->resourceProviderMock
            ]
        );
    }

    public function testConvert()
    {
        $aclResources = require __DIR__ . '/_files/acl.php';
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/integration.xml');
        $expectedResult = require __DIR__ . '/_files/integration.php';
        $this->resourceProviderMock->expects($this->once())->method('getAclResources')->willReturn($aclResources);

        $this->assertEquals($expectedResult, $this->model->convert($inputData));
    }
}
