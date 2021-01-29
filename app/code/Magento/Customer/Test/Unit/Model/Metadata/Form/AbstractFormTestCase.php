<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

/** Test Magento\Customer\Model\Metadata\Form\Multiline */
abstract class AbstractFormTestCase extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Stdlib\DateTime\TimezoneInterface */
    protected $localeMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Locale\ResolverInterface */
    protected $localeResolverMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Psr\Log\LoggerInterface */
    protected $loggerMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Api\Data\AttributeMetadataInterface */
    protected $attributeMetadataMock;

    protected function setUp(): void
    {
        $this->localeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->getMock();
        $this->localeResolverMock = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();
        $this->attributeMetadataMock = $this->createMock(\Magento\Customer\Api\Data\AttributeMetadataInterface::class);
    }
}
