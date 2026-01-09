<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column\Online\Website;

use Magento\Customer\Ui\Component\Listing\Column\Online\Website\Options;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\System\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Website Options
 */
class OptionsTest extends TestCase
{
    /**
     * @var Options
     */
    private $model;

    /**
     * @var Store|MockObject
     */
    private $systemStoreMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->systemStoreMock = $this->createMock(Store::class);
        $this->model = $objectManager->getObject(
            Options::class,
            ['systemStore' => $this->systemStoreMock]
        );
    }

    /**
     * Test toOptionArray returns website options
     */
    public function testToOptionArray(): void
    {
        $expectedOptions = [
            [
                'value' => '1',
                'label' => 'Main Website'
            ],
            [
                'value' => '2',
                'label' => 'Second Website'
            ]
        ];

        $this->systemStoreMock->expects($this->once())
            ->method('getWebsiteValuesForForm')
            ->willReturn($expectedOptions);

        $this->assertEquals($expectedOptions, $this->model->toOptionArray());
    }
}
