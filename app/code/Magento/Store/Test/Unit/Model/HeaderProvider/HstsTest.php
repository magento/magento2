<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\HeaderProvider;

use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\HeaderProvider\Hsts;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HstsTest extends TestCase
{
    /** Strict-Transport-Security (HSTS) Header name */
    const HEADER_NAME = 'Strict-Transport-Security';

    /**
     * Strict-Transport-Security (HSTS) header value
     */
    const HEADER_VALUE = 'max-age=31536000';

    /**
     * @var Hsts
     */
    protected $object;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManagerHelper($this);
        $this->object = $objectManager->getObject(
            Hsts::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    public function testGetName()
    {
        $this->assertEquals($this::HEADER_NAME, $this->object->getName(), 'Wrong header name');
    }

    public function testGetValue()
    {
        $this->assertEquals($this::HEADER_VALUE, $this->object->getValue(), 'Wrong header value');
    }

    /**
     * @param [] $configValuesMap
     * @param bool $expected
     * @dataProvider canApplyDataProvider
     */
    public function testCanApply($configValuesMap, $expected)
    {
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturnMap(
            $configValuesMap
        );
        $this->assertEquals($expected, $this->object->canApply(), 'Incorrect canApply result');
    }

    /**
     * Data provider for testCanApply test
     *
     * @return array
     */
    public static function canApplyDataProvider()
    {
        return [
            [
                [
                    [Store::XML_PATH_SECURE_IN_FRONTEND, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true],
                    [Store::XML_PATH_SECURE_IN_ADMINHTML, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true],
                    [Store::XML_PATH_ENABLE_HSTS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true]
                ],
                true
            ],
            [
                [
                    [Store::XML_PATH_SECURE_IN_FRONTEND, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false],
                    [Store::XML_PATH_SECURE_IN_ADMINHTML, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true],
                    [Store::XML_PATH_ENABLE_HSTS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true]
                ],
                false
            ],
            [
                [
                    [Store::XML_PATH_SECURE_IN_FRONTEND, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true],
                    [Store::XML_PATH_SECURE_IN_ADMINHTML, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false],
                    [Store::XML_PATH_ENABLE_HSTS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true]
                ],
                false
            ],
            [
                [
                    [Store::XML_PATH_SECURE_IN_FRONTEND, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true],
                    [Store::XML_PATH_SECURE_IN_ADMINHTML, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true],
                    [Store::XML_PATH_ENABLE_HSTS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false]
                ],
                false
            ],
            [
                [
                    [Store::XML_PATH_SECURE_IN_FRONTEND, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false],
                    [Store::XML_PATH_SECURE_IN_ADMINHTML, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true],
                    [Store::XML_PATH_ENABLE_HSTS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false]
                ],
                false
            ],
            [
                [
                    [Store::XML_PATH_SECURE_IN_FRONTEND, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true],
                    [Store::XML_PATH_SECURE_IN_ADMINHTML, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false],
                    [Store::XML_PATH_ENABLE_HSTS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false]
                ],
                false
            ],
            [
                [
                    [Store::XML_PATH_SECURE_IN_FRONTEND, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false],
                    [Store::XML_PATH_SECURE_IN_ADMINHTML, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false],
                    [Store::XML_PATH_ENABLE_HSTS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false]
                ],
                false
            ],
            [
                [
                    [Store::XML_PATH_SECURE_IN_FRONTEND, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false],
                    [Store::XML_PATH_SECURE_IN_ADMINHTML, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, false],
                    [Store::XML_PATH_ENABLE_HSTS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT , null, true]
                ],
                false
            ],
        ];
    }
}
