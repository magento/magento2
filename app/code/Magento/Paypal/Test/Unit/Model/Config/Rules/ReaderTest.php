<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Config\Rules;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Paypal\Helper\Backend;
use Magento\Paypal\Model\Config\Rules\Converter;
use Magento\Paypal\Model\Config\Rules\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /** @var  Reader */
    protected $reader;

    /** @var  FileResolverInterface|MockObject */
    protected $fileResolver;

    /** @var  Converter|MockObject */
    protected $converter;

    /** @var  SchemaLocatorInterface|MockObject */
    protected $schemaLocator;

    /** @var  ValidationStateInterface|MockObject */
    protected $validationState;

    /** @var Backend|MockObject */
    protected $helper;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->fileResolver = $this->getMockForAbstractClass(
            FileResolverInterface::class
        );
        $this->converter = $this->createMock(Converter::class);
        $this->schemaLocator = $this->getMockForAbstractClass(
            SchemaLocatorInterface::class
        );
        $this->validationState = $this->getMockForAbstractClass(
            ValidationStateInterface::class
        );
        $this->helper = $this->createMock(Backend::class);
    }

    /**
     * @param string $countryCode
     * @param string $xml
     * @param string $expected
     * @dataProvider dataProviderReadExistingCountryConfig
     */
    public function testReadExistingCountryConfig($countryCode, $xml, $expected)
    {
        $this->helper->expects($this->once())
            ->method('getConfigurationCountryCode')
            ->willReturn($countryCode);

        $this->fileResolver->expects($this->once())
            ->method('get')
            ->with($expected)
            ->willReturn($xml);

        $this->reader = new Reader(
            $this->fileResolver,
            $this->converter,
            $this->schemaLocator,
            $this->validationState,
            $this->helper
        );

        $this->reader->read();
    }

    /**
     * @param string $countryCode
     * @param string $xml
     * @param string $expected
     * @dataProvider dataProviderReadOtherCountryConfig
     */
    public function testReadOtherCountryConfig($countryCode, $xml, $expected)
    {
        $this->helper->expects($this->once())
            ->method('getConfigurationCountryCode')
            ->willReturn($countryCode);

        $this->fileResolver->expects($this->at(0))
            ->method('get')
            ->willReturn([]);
        $this->fileResolver->expects($this->at(1))
            ->method('get')
            ->with($expected)
            ->willReturn($xml);

        $this->reader = new Reader(
            $this->fileResolver,
            $this->converter,
            $this->schemaLocator,
            $this->validationState,
            $this->helper
        );

        $this->reader->read();
    }

    /**
     * @return array
     */
    public function dataProviderReadExistingCountryConfig()
    {
        return [
            ['us', ['<payment/>'], 'adminhtml/rules/payment_us.xml'],
            ['ca', ['<payment/>'], 'adminhtml/rules/payment_ca.xml'],
            ['au', ['<payment/>'], 'adminhtml/rules/payment_au.xml'],
            ['gb', ['<payment/>'], 'adminhtml/rules/payment_gb.xml'],
            ['jp', ['<payment/>'], 'adminhtml/rules/payment_jp.xml'],
            ['fr', ['<payment/>'], 'adminhtml/rules/payment_fr.xml'],
            ['it', ['<payment/>'], 'adminhtml/rules/payment_it.xml'],
            ['es', ['<payment/>'], 'adminhtml/rules/payment_es.xml'],
            ['hk', ['<payment/>'], 'adminhtml/rules/payment_hk.xml'],
            ['nz', ['<payment/>'], 'adminhtml/rules/payment_nz.xml'],
            ['de', ['<payment/>'], 'adminhtml/rules/payment_de.xml'],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderReadOtherCountryConfig()
    {
        return [
            ['no', ['<payment/>'], 'adminhtml/rules/payment_other.xml'],
        ];
    }
}
