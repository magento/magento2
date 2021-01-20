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
class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Converter
     */
    protected $model;

    /** @var \Magento\Framework\Acl\AclResource\ProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $resourceProviderMock;

    protected function setUp(): void
    {
        $this->resourceProviderMock = $this->getMockBuilder(\Magento\Framework\Acl\AclResource\ProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Integration\Model\Config\Consolidated\Converter::class,
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
