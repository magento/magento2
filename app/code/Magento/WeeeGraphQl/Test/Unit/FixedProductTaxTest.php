<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WeeeGraphQl\Test\Unit;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\WeeeGraphQl\Model\Resolver\FixedProductTax;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class FixedProductTaxTest extends TestCase
{
    /**
     * @var FixedProductTax
     */
    private $resolver;

    /**
     * Build the Testing Environment
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(FixedProductTax::class);
    }

    public function testExceptionWhenNoModelSpecified(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageRegExp('/value should be specified/');

        $this->resolver->resolve(
            $this->getFieldStub(),
            null,
            $this->getResolveInfoStub()
        );
    }

    /**
     * Returns stub for Field
     *
     * @return MockObject|Field
     */
    private function getFieldStub(): Field
    {
        /** @var MockObject|Field $fieldMock */
        $fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $fieldMock;
    }

    /**
     * Returns stub for ResolveInfo
     *
     * @return MockObject|ResolveInfo
     */
    private function getResolveInfoStub(): ResolveInfo
    {
        /** @var MockObject|ResolveInfo $resolveInfoMock */
        $resolveInfoMock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $resolveInfoMock;
    }
}
