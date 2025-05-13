<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Config\Consolidated;

use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Model\Config\Consolidated\Converter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for conversion of integration XML config into array representation.
 */
class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $model;

    /** @var ProviderInterface|MockObject */
    protected $resourceProviderMock;

    protected function setUp(): void
    {
        $this->resourceProviderMock = $this->getMockBuilder(ProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Converter::class,
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
