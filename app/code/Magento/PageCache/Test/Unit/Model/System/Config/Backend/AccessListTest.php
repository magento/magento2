<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\System\Config\Backend;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\System\Config\Backend\AccessList;
use PHPUnit\Framework\TestCase;

class AccessListTest extends TestCase
{
    /**
     * @var AccessList
     */
    private $accessList;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $configMock = $this->getMockForAbstractClass(
            ScopeConfigInterface::class
        );
        $configMock->expects($this->any())
            ->method('getValue')
            ->with('system/full_page_cache/default')
            ->willReturn(['access_list' => 'localhost']);
        $this->accessList = $objectManager->getObject(
            AccessList::class,
            [
                'config' => $configMock,
                'data' => ['field' => 'access_list']
            ]
        );
    }

    /**
     * @return array
     */
    public static function getValidValues(): array
    {
        return [
            ['localhost', 'localhost'],
            [null, 'localhost'],
            ['127.0.0.1', '127.0.0.1'],
            ['127.0.0.1, localhost, ::2', '127.0.0.1, localhost, ::2'],
        ];
    }

    /**
     * @param mixed $value
     * @param mixed $expectedValue
     * @dataProvider getValidValues
     */
    public function testBeforeSave($value, $expectedValue)
    {
        $this->accessList->setValue($value);
        $this->accessList->beforeSave();
        $this->assertEquals($expectedValue, $this->accessList->getValue());
    }

    /**
     * @return array
     */
    public static function getInvalidValues(): array
    {
        return [
            ['\\bull val\\'],
            ['{*I am not an IP*}'],
            ['{*I am not an IP*}, 127.0.0.1'],
        ];
    }

    /**
     * @param mixed $value
     * @dataProvider getInvalidValues
     */
    public function testBeforeSaveInvalid($value)
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->accessList->setValue($value);
        $this->accessList->beforeSave();
    }
}
