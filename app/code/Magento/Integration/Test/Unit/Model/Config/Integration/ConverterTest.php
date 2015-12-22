<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Config\Integration;

/**
 * Test for conversion of integration API XML config into array representation.
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Model\Config\Integration\Converter
     */
    protected $model;

    /** @var \Magento\Framework\Acl\AclResource\ProviderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceProviderMock;

    /** @var \Magento\Integration\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $integrationDataMock;

    public function setUp()
    {
        $this->resourceProviderMock = $this->getMockBuilder('Magento\Framework\Acl\AclResource\ProviderInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->integrationDataMock = $this->getMockBuilder('Magento\Integration\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Integration\Model\Config\Integration\Converter',
            [
                'resourceProvider' => $this->resourceProviderMock,
                'integrationData' => $this->integrationDataMock
            ]
        );
    }

    public function testConvert()
    {
        $aclResources = [[], [
            'children' => [
                'data'
            ]
        ]];
        $aclHashTree = ['data'];
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/api.xml');
        $expectedResult = require __DIR__ . '/_files/api.php';
        $this->resourceProviderMock->expects($this->once())->method('getAclResources')->willReturn($aclResources);
        $this->integrationDataMock->expects($this->once())->method('hashResources')->willReturn($aclHashTree);
        $this->integrationDataMock->expects($this->at(1))->method('addParents')
            ->willReturn([$expectedResult['TestIntegration1']['resource'][0],
                          $expectedResult['TestIntegration1']['resource'][1]]);
        $this->integrationDataMock->expects($this->at(2))->method('addParents')
            ->willReturn([$expectedResult['TestIntegration1']['resource'][2],
                          $expectedResult['TestIntegration1']['resource'][3]]);
        $this->integrationDataMock->expects($this->at(3))->method('addParents')
            ->willReturn([$expectedResult['TestIntegration2']['resource'][0]]);
        $this->assertEquals($expectedResult, $this->model->convert($inputData));
    }
}
