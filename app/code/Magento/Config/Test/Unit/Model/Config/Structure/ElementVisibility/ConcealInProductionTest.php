<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\ElementVisibility;

use Magento\Config\Model\Config\Structure\ElementVisibility\ConcealInProduction;
use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;
use Magento\Framework\App\State;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConcealInProductionTest extends TestCase
{
    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @var ConcealInProduction
     */
    private $model;

    protected function setUp(): void
    {
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configs = [
            'section1/group1/field1' => ElementVisibilityInterface::DISABLED,
            'section1/group1' => ElementVisibilityInterface::HIDDEN,
            'section1' => ElementVisibilityInterface::DISABLED,
            'section1/group2' => 'no',
            'section2/group1' => ElementVisibilityInterface::DISABLED,
            'section2/group2' => ElementVisibilityInterface::HIDDEN,
            'section3' => ElementVisibilityInterface::HIDDEN,
            'section3/group1/field1' => 'no',
        ];
        $exemptions = [
            'section1/group1/field3' => '',
            'section1/group2/field1' => '',
            'section2/group2/field1' => '',
            'section3/group2' => '',
        ];

        $this->model = new ConcealInProduction($this->stateMock, $configs, $exemptions);
    }

    /**
     * @param string $path
     * @param string $mageMode
     * @param bool $isDisabled
     * @param bool $isHidden
     * @dataProvider disabledDataProvider
     */
    public function testCheckVisibility(string $path, string $mageMode, bool $isHidden, bool $isDisabled): void
    {
        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->willReturn($mageMode);

        $this->assertSame($isHidden, $this->model->isHidden($path));
        $this->assertSame($isDisabled, $this->model->isDisabled($path));
    }

    /**
     * @return array
     */
    public function disabledDataProvider(): array
    {
        return [
            //visibility of field 'section1/group1/field1' should be applied
            ['section1/group1/field1', State::MODE_PRODUCTION, false, true],
            ['section1/group1/field1', State::MODE_DEFAULT, false, false],
            ['section1/group1/field1', State::MODE_DEVELOPER, false, false],
            //visibility of group 'section1/group1' should be applied
            ['section1/group1/field2', State::MODE_PRODUCTION, true, false],
            ['section1/group1/field2', State::MODE_DEFAULT, false, false],
            ['section1/group1/field2', State::MODE_DEVELOPER, false, false],
            //exemption should be applied for section1/group2/field1
            ['section1/group2/field1', State::MODE_PRODUCTION, false, false],
            ['section1/group2/field1', State::MODE_DEFAULT, false, false],
            ['section1/group2/field1', State::MODE_DEVELOPER, false, false],
            //as 'section1/group2' has neither Disable nor Hidden rule, this field should be visible
            ['section1/group2/field2', State::MODE_PRODUCTION, false, false],
            //exemption should be applied for section1/group1/field3
            ['section1/group1/field3', State::MODE_PRODUCTION, false, false],
            //visibility of group 'section2/group1' should be applied
            ['section2/group1/field1', State::MODE_PRODUCTION, false, true],
            //exemption should be applied for section2/group2/field1
            ['section2/group2/field1', State::MODE_PRODUCTION, false, false],
            //any rule should not be applied
            ['section2/group3/field1', State::MODE_PRODUCTION, false, false],
            //any rule should not be applied
            ['section3/group1/field1', State::MODE_PRODUCTION, false, false],
            //visibility of section 'section3' should be applied
            ['section3/group1/field2', State::MODE_PRODUCTION, true, false],
            //exception from 'section3/group2' should be applied
            ['section3/group2/field1', State::MODE_PRODUCTION, false, false],

        ];
    }
}
