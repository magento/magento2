<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Customer\Model\Vat testing
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Vat;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VatTest extends TestCase
{
    /**
     * @var Vat
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var PsrLogger
     */
    protected $logger;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->logger = $this->getMockForAbstractClass(PsrLogger::class);

        $this->model = new Vat(
            $this->scopeConfigMock,
            $this->logger
        );
    }

    /**
     * @dataProvider vatNumberDataProvider
     */
    public function testCheckVatNumber($countryCode, $vatNumber, $expected)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with('general/country/eu_countries', ScopeInterface::SCOPE_STORE)
            ->willReturn('DE, GB, US');

        $result = $this->model->checkVatNumber($countryCode, $vatNumber)->getData();
        $resultText = $result['request_message']->getText();

        $this->assertEquals($expected, $resultText);
    }

    /**
     * Test for canCheckVatNumber
     */
    public function testCanCheckVatNumber()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with('general/country/eu_countries', ScopeInterface::SCOPE_STORE)
            ->willReturn('DE, GB, US');

        $this->assertTrue($this->model->canCheckVatNumber('DE', '265675123', '', ''));
        $this->assertTrue($this->model->canCheckVatNumber('GB', 'GB571903731', '', ''));
        $this->assertTrue($this->model->canCheckVatNumber('GB', '571903731', '', ''));
        $this->assertFalse($this->model->canCheckVatNumber('US', '123456789', '', ''));
    }

    /**
     * @return array
     */
    public static function vatNumberDataProvider(): array
    {
        return [
            ['DE', '265675123', 'VAT Number is valid.'],
            ['DE', 'DE265675123', 'VAT Number is valid.'],
            ['GB', 'GB571903731', 'VAT Number is valid.'],
            ['GB', '571903731', 'VAT Number is valid.'],
            ['US', '123456789', 'Error during VAT Number verification.'],
            ['US', 'US123456789', 'Error during VAT Number verification.'],
        ];
    }
}
