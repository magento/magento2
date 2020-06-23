<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/** Test Magento\Customer\Model\Metadata\Form\Multiline */
abstract class AbstractFormTestCase extends TestCase
{
    /** @var MockObject|TimezoneInterface */
    protected $localeMock;

    /** @var MockObject|ResolverInterface */
    protected $localeResolverMock;

    /** @var MockObject|LoggerInterface */
    protected $loggerMock;

    /** @var MockObject|AttributeMetadataInterface */
    protected $attributeMetadataMock;

    protected function setUp(): void
    {
        $this->localeMock = $this->getMockBuilder(TimezoneInterface::class)
            ->getMock();
        $this->localeResolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->attributeMetadataMock = $this->getMockForAbstractClass(AttributeMetadataInterface::class);
    }
}
