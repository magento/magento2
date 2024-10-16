<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Attribute\Data;

use Magento\Customer\Model\Attribute\Data\Postcode;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PostcodeTest extends TestCase
{
    /**
     * @var DirectoryHelper|MockObject
     */
    protected $directoryHelperMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    protected $attributeMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeMock;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolverMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->localeMock = $this->getMockBuilder(TimezoneInterface::class)
            ->getMock();
        $this->localeResolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->directoryHelperMock = $this->getMockBuilder(\Magento\Directory\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStoreLabel'])
            ->getMock();
    }

    /**
     * @param string $value to assign to boolean
     * @param bool $expected text output
     * @param string $countryId
     * @param bool $isOptional
     *
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $expected, $countryId, $isOptional)
    {
        $storeLabel = 'Zip/Postal Code';
        $this->attributeMock->expects($this->any())
            ->method('getStoreLabel')
            ->willReturn($storeLabel);

        $this->directoryHelperMock->expects($this->once())
            ->method('isZipCodeOptional')
            ->willReturnMap([
                [$countryId, $isOptional],
            ]);

        $object = new Postcode(
            $this->localeMock,
            $this->loggerMock,
            $this->localeResolverMock,
            $this->directoryHelperMock
        );
        $object->setAttribute($this->attributeMock);
        $object->setExtractedData(['country_id' => $countryId]);

        $actual = $object->validateValue($value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function validateValueDataProvider()
    {
        return [
            ['', ['"Zip/Postal Code" is a required value.'], 'US', false],
            ['90034', true, 'US', false],
            ['', true, 'IE', true],
            ['90034', true, 'IE', true],
        ];
    }
}
