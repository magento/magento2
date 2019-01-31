<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\ElementVisibility;

use Magento\Framework\App\DeploymentConfig;
use \Magento\Config\Model\Config\Structure\ElementVisibility\ConcealInProduction;
use \Magento\Config\Model\Config\Structure\ElementVisibility\ConcealInProductionFactory;
use Magento\Framework\Config\ConfigOptionsListConstants as Constants;
use Magento\Config\Model\Config\Structure\ElementVisibility\ConcealInProductionWithoutScdOnDemand;
use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;

class ConcealInProductionWithoutScdOnDemandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConcealInProduction|\PHPUnit_Framework_MockObject_MockObject
     */
    private $concealInProductionMock;

    /**
     * @var ConcealInProductionWithoutScdOnDemand
     */
    private $model;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    protected function setUp()
    {
        $concealInProductionFactoryMock = $this->createMock(ConcealInProductionFactory::class);

        $this->concealInProductionMock = $this->createMock(ConcealInProduction::class);

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
        ];
        $exemptions = [
            'section1/group1/field3' => '',
            'section1/group2/field1' => '',
            'section2/group2/field1' => '',
            'section3/group2' => '',
        ];

        $concealInProductionFactoryMock->expects($this->any())
            ->method('create')
            ->with(['configs' => $configs, 'exemptions' => $exemptions])
            ->willReturn($this->concealInProductionMock);

        $this->model = new ConcealInProductionWithoutScdOnDemand(
            $concealInProductionFactoryMock,
            $this->deploymentConfigMock,
            $configs,
            $exemptions
        );
    }

    public function testIsHiddenScdOnDemandEnabled(): void
    {
        $path = 'section1/group1/field1';
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            ->willReturn(true);
        $this->concealInProductionMock->expects($this->never())
            ->method('isHidden');

        $this->assertFalse($this->model->isHidden($path));
    }

    public function testIsDisabledScdOnDemandEnabled(): void
    {
        $path = 'section1/group1/field1';
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            ->willReturn(true);
        $this->concealInProductionMock->expects($this->never())
            ->method('isDisabled');

        $this->assertFalse($this->model->isDisabled($path));
    }

    /**
     * @param bool $isHidden
     *
     * @dataProvider visibilityDataProvider
     */
    public function testIsHiddenScdOnDemandDisabled(bool $isHidden): void
    {
        $path = 'section1/group1/field1';
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            ->willReturn(false);
        $this->concealInProductionMock->expects($this->once())
            ->method('isHidden')
            ->with($path)
            ->willReturn($isHidden);

        $this->assertSame($isHidden, $this->model->isHidden($path));
    }

    /**
     * @param bool $isDisabled
     *
     * @dataProvider visibilityDataProvider
     */
    public function testIsDisabledScdOnDemandDisabled(bool $isDisabled): void
    {
        $path = 'section1/group1/field1';
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            ->willReturn(false);
        $this->concealInProductionMock->expects($this->once())
            ->method('isDisabled')
            ->with($path)
            ->willReturn($isDisabled);

        $this->assertSame($isDisabled, $this->model->isDisabled($path));
    }

    /**
     * @return array
     */
    public function visibilityDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
