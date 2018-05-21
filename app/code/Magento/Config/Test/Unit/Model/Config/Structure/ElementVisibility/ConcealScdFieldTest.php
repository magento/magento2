<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\ElementVisibility;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Config\ConfigOptionsListConstants as Constants;
use Magento\Config\Model\Config\Structure\ElementVisibility\ConcealScdField;
use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;

class ConcealScdFieldTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

    /**
     * @var ConcealScdField
     */
    private $model;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    protected function setUp()
    {
        $this->stateMock = $this->createMock(State::class);

        $this->deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);

        $configs = [
            'section1/group1/field1' => ElementVisibilityInterface::DISABLED,
            'section1/group1' => ElementVisibilityInterface::HIDDEN,
            'section1' => ElementVisibilityInterface::DISABLED,
            'section1/group2' => 'no',
            'section2/group1' => ElementVisibilityInterface::DISABLED,
            'section2/group2' => ElementVisibilityInterface::HIDDEN,
            'section3' => ElementVisibilityInterface::HIDDEN,
            'section3/group1/field1' => 'no',
            'section5' => 'no',
        ];
        $exemptions = [
            'section1/group1/field3' => '',
            'section1/group2/field1' => '',
            'section2/group2/field1' => '',
            'section3/group2' => '',
        ];

        $this->model = new ConcealScdField($this->stateMock, $this->deploymentConfigMock, $configs, $exemptions);
    }

    /**
     * @param string $path
     * @param string $mageMode
     * @param int $scdOnDemand
     * @param bool $isDisabled
     * @param bool $isHidden
     * @dataProvider disabledDataProvider
     */
    public function testCheckVisibility($path, $mageMode, $scdOnDemand, $isHidden, $isDisabled)
    {
        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->willReturn($mageMode);
        $this->deploymentConfigMock->expects($this->any())
            ->method('getConfigData')
            ->with(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            ->willReturn($scdOnDemand);

        $this->assertSame($isHidden, $this->model->isHidden($path));
        $this->assertSame($isDisabled, $this->model->isDisabled($path));
    }

    /**
     * @return array
     */
    public function disabledDataProvider()
    {
        return [
            //visibility of field 'section1/group1/field1' should be applied
            ['section1/group1/field1', State::MODE_PRODUCTION, 1, false, false],
            ['section1/group1/field1', State::MODE_PRODUCTION, 0, false, true],
            ['section1/group1/field1', State::MODE_DEFAULT, 0, false, false],
            ['section1/group1/field1', State::MODE_DEFAULT, 1, false, false],
            ['section1/group1/field1', State::MODE_DEVELOPER, 0, false, false],
            ['section1/group1/field1', State::MODE_DEVELOPER, 1, false, false],
            //visibility of group 'section1/group1' should be applied
            ['section1/group1/field2', State::MODE_PRODUCTION, 1, false, false],
            ['section1/group1/field2', State::MODE_PRODUCTION, 0, true, false],
            ['section1/group1/field2', State::MODE_DEFAULT, 0, false, false],
            ['section1/group1/field2', State::MODE_DEFAULT, 1, false, false],
            ['section1/group1/field2', State::MODE_DEVELOPER, 0, false, false],
            ['section1/group1/field2', State::MODE_DEVELOPER, 1, false, false],
            //exemption should be applied for section1/group2/field1
            ['section1/group2/field1', State::MODE_PRODUCTION, 1, false, false],
            ['section1/group2/field1', State::MODE_PRODUCTION, 0, false, false],
            ['section1/group2/field1', State::MODE_DEFAULT, 0, false, false],
            ['section1/group2/field1', State::MODE_DEFAULT, 1, false, false],
            ['section1/group2/field1', State::MODE_DEVELOPER, 0, false, false],
            ['section1/group2/field1', State::MODE_DEVELOPER, 1, false, false],
            //visibility of section 'section1' should be applied
//            ['section1/group2/field2', State::MODE_PRODUCTION, 1, false, false],
//            ['section1/group2/field2', State::MODE_PRODUCTION, 0, false, true],
//            ['section1/group2/field2', State::MODE_DEFAULT, 0, false, false],
//            ['section1/group2/field2', State::MODE_DEFAULT, 1, false, false],
//            ['section1/group2/field2', State::MODE_DEVELOPER, 0, false, false],
//            ['section1/group2/field2', State::MODE_DEVELOPER, 1, false, false],
//            //exemption should be applied for section1/group1/field3
//            ['section1/group1/field3', State::MODE_PRODUCTION, 1, false, false],
//            ['section1/group1/field3', State::MODE_PRODUCTION, 0, false, false],
//            ['section1/group1/field3', State::MODE_DEFAULT, 0, false, false],
//            ['section1/group1/field3', State::MODE_DEFAULT, 1, false, false],
//            ['section1/group1/field3', State::MODE_DEVELOPER, 0, false, false],
//            ['section1/group1/field3', State::MODE_DEVELOPER, 1, false, false],
//            //visibility of group 'section2/group1' should be applied
//            ['section2/group1/field1', State::MODE_PRODUCTION, 1, false, false],
//            ['section2/group1/field1', State::MODE_PRODUCTION, 0, false, true],
//            //exemption should be applied for section2/group2/field1
//            ['section2/group2/field1', State::MODE_PRODUCTION, 1, false, false],
//            ['section2/group2/field1', State::MODE_PRODUCTION, 0, false, false],
//            //any rule should not be applied
//            ['section2/group3/field1', State::MODE_PRODUCTION, 1, false, false],
//            ['section2/group3/field1', State::MODE_PRODUCTION, 0, false, false],

        ];
    }

//    /**
//     * @param string $path
//     * @param string $mageMode
//     * @param bool $expectedResult
//     * @dataProvider hiddenDataProvider
//     */
//    public function testIsHidden($path, $mageMode, $expectedResult)
//    {
//        $this->stateMock->expects($this->once())
//            ->method('getMode')
//            ->willReturn($mageMode);
//        $this->assertSame($expectedResult, $this->model->isHidden($path));
//    }
//
//    /**
//     * @return array
//     */
//    public function hiddenDataProvider()
//    {
//        return [
//            ['first/path', State::MODE_PRODUCTION, false],
//            ['first/path', State::MODE_DEFAULT, false],
//            ['some/path', State::MODE_PRODUCTION, false],
//            ['second/path/field', State::MODE_PRODUCTION, true],
//            ['second/path', State::MODE_DEVELOPER, false],
//            ['fourth/path/value', State::MODE_PRODUCTION, false],
//            ['fourth/path/test', State::MODE_PRODUCTION, true],
//        ];
//    }
}
