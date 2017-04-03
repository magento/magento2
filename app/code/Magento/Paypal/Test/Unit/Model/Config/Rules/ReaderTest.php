<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Config\Rules;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Paypal\Helper\Backend;
use Magento\Paypal\Model\Config\Rules\Converter;
use Magento\Paypal\Model\Config\Rules\Reader;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Reader */
    protected $reader;

    /** @var  FileResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileResolver;

    /** @var  Converter|\PHPUnit_Framework_MockObject_MockObject */
    protected $converter;

    /** @var  SchemaLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $schemaLocator;

    /** @var  ValidationStateInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $validationState;

    /** @var Backend|\PHPUnit_Framework_MockObject_MockObject */
    protected $helper;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fileResolver = $this->getMockForAbstractClass(
            \Magento\Framework\Config\FileResolverInterface::class
        );
        $this->converter = $this->getMock(
            \Magento\Paypal\Model\Config\Rules\Converter::class,
            [],
            [],
            '',
            false
        );
        $this->schemaLocator = $this->getMockForAbstractClass(
            \Magento\Framework\Config\SchemaLocatorInterface::class
        );
        $this->validationState = $this->getMockForAbstractClass(
            \Magento\Framework\Config\ValidationStateInterface::class
        );
        $this->helper = $this->getMock(
            \Magento\Paypal\Helper\Backend::class,
            [],
            [],
            '',
            false
        );
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
            ->with($this->equalTo($expected))
            ->willReturn($xml);

        $this->reader = new \Magento\Paypal\Model\Config\Rules\Reader(
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
            ->with($this->equalTo($expected))
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
