<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Entity;

use Magento\Catalog\Model\Entity\GetProductCustomAttributeCodes;
use Magento\Eav\Model\Entity\GetCustomAttributeCodesInterface;
use Magento\Framework\Api\MetadataServiceInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for GetProductCustomAttributeCodes entity model.
 */
class GetProductCustomAttributeCodesTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var GetProductCustomAttributeCodes
     */
    private $getProductCustomAttributeCodes;

    /**
     * @var GetCustomAttributeCodesInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $baseCustomAttributeCodes;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->baseCustomAttributeCodes = $this->getMockBuilder(GetCustomAttributeCodesInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMockForAbstractClass();
        $objectManager = new ObjectManager($this);
        $this->getProductCustomAttributeCodes = $objectManager->getObject(
            GetProductCustomAttributeCodes::class,
            ['baseCustomAttributeCodes' => $this->baseCustomAttributeCodes]
        );
    }

    /**
     * Test GetProductCustomAttributeCodes::execute() will return only custom product attribute codes.
     */
    public function testExecute()
    {
        /** @var MetadataServiceInterface|\PHPUnit_Framework_MockObject_MockObject $metadataService */
        $metadataService = $this->getMockBuilder(MetadataServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->baseCustomAttributeCodes->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($metadataService))
            ->willReturn(['test_custom_attribute_code', 'name']);
        $this->assertEquals(
            ['test_custom_attribute_code'],
            $this->getProductCustomAttributeCodes->execute($metadataService)
        );
    }
}
