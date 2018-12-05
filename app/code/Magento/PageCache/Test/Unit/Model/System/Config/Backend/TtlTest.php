<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\System\Config\Backend;

use Magento\PageCache\Model\System\Config\Backend\Ttl;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;

/**
 * Class for tesing backend model for processing Public content cache lifetime settings.
 */
class TtlTest extends TestCase
{
    /**
     * @var Ttl
     */
    private $ttl;

    /*
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaperMock;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $configMock->expects($this->any())
            ->method('getValue')
            ->with('system/full_page_cache/default')
            ->willReturn(['ttl' => 86400]);

        $this->escaperMock = $this->getMockBuilder(Escaper::class)->disableOriginalConstructor()->getMock();

        $this->ttl = $objectManager->getObject(
            Ttl::class,
            [
                'config' => $configMock,
                'data' => ['field' => 'ttl'],
                'escaper' => $this->escaperMock,
            ]
        );
    }

    /**
     * @return array
     */
    public function getValidValues(): array
    {
        return [
            ['3600', '3600'],
            ['10000', '10000'],
            ['100000', '100000'],
            ['1000000', '1000000'],
        ];
    }

    /**
     * @param string $value
     * @param string $expectedValue
     * @return void
     * @dataProvider getValidValues
     */
    public function testBeforeSave(string $value, string $expectedValue)
    {
        $this->ttl->setValue($value);
        $this->ttl->beforeSave();
        $this->assertEquals($expectedValue, $this->ttl->getValue());
    }

    /**
     * @return array
     */
    public function getInvalidValues(): array
    {
        return [
            ['<script>alert(1)</script>'],
            ['apple'],
            ['123 street'],
            ['-123'],
        ];
    }

    /**
     * @param string $value
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessageRegExp /Ttl value ".+" is not valid. Please .+ only numbers equal or greater than zero./
     * @dataProvider getInvalidValues
     */
    public function testBeforeSaveInvalid(string $value)
    {
        $this->ttl->setValue($value);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->with($value)->willReturn($value);
        $this->ttl->beforeSave();
    }
}
