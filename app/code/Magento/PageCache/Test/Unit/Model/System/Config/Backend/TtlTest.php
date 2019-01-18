<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Unit\Model\System\Config\Backend;

use Magento\PageCache\Model\System\Config\Backend\Ttl;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;

class TtlTest extends \PHPUnit_Framework_TestCase
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
    public function getValidValues()
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
    public function testBeforeSave($value, $expectedValue)
    {
        $this->ttl->setValue($value);
        $this->ttl->beforeSave();
        $this->assertEquals($expectedValue, $this->ttl->getValue());
    }

    /**
     * @return array
     */
    public function getInvalidValues()
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
     * @dataProvider getInvalidValues
     */
    public function testBeforeSaveInvalid($value)
    {
        $this->ttl->setValue($value);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->with($value)->willReturn($value);
        $expMessage = sprintf(
            'Ttl value "%s" is not valid. Please use only numbers equal or greater than zero.',
            $value
        );
        $this->setExpectedException(LocalizedException::class, $expMessage);
        $this->ttl->beforeSave();
    }
}
