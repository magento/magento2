<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\Entity;

use Magento\Eav\Model\Entity\GetCustomAttributeCodes;
use Magento\Framework\Api\MetadataObjectInterface;
use Magento\Framework\Api\MetadataServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for GetCustomAttributeCodes entity model.
 */
class GetCustomAttributeCodesTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var GetCustomAttributeCodes
     */
    private $getCustomAttributeCodes;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->getCustomAttributeCodes = new GetCustomAttributeCodes();
    }

    /**
     * Test GetCustomAttributeCodes::execute() will return attribute codes from attributes metadata.
     *
     * @return void
     */
    public function testExecute()
    {
        $attributeCode = 'testCode';
        $attributeMetadata = $this->getMockBuilder(MetadataObjectInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMockForAbstractClass();
        $attributeMetadata->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        /** @var MetadataServiceInterface|\PHPUnit_Framework_MockObject_MockObject $metadataService */
        $metadataService = $this->getMockBuilder(MetadataServiceInterface::class)
            ->setMethods(['getCustomAttributesMetadata'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $metadataService->expects($this->once())
            ->method('getCustomAttributesMetadata')
            ->willReturn([$attributeMetadata]);
        $this->assertEquals([$attributeCode], $this->getCustomAttributeCodes->execute($metadataService));
    }
}
