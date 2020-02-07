<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model\Mode;

use Magento\Csp\Model\Mode\ConfigManager;
use Magento\Csp\Model\Mode\Data\ModeConfigured;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for \Magento\Csp\Model\Mode\ConfigManager
 */
class ConfigManagerTest extends TestCase
{
    const STUB_REPORT_ONLY = true;
    const STUB_AREA_CODE_OTHER = 'other';

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $configMock;

    /**
     * @var MockObject|Store
     */
    private $storeModelMock;

    /**
     * @var MockObject|State
     */
    private $stateMock;

    /**
     * @var ConfigManager
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->configMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeModelMock = $this->createMock(Store::class);
        $this->stateMock = $this->createMock(State::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            ConfigManager::class,
            [
                'config' => $this->configMock,
                'storeModel' => $this->storeModelMock,
                'state' => $this->stateMock
            ]
        );
    }

    /**
     * Test case with correct Area codes.
     *
     * @param string $area
     * @param string $pathReportOnly
     * @param string $pathReportUri
     * @dataProvider dataProviderGetConfiguredWithCorrectArea
     */
    public function testGetConfiguredWithCorrectArea(string $area, string $pathReportOnly, string $pathReportUri)
    {
        $this->stateMock->expects($this->once())->method('getAreaCode')->willReturn($area);

        $this->configMock->expects($this->once())->method('getValue')->with($pathReportUri);
        $this->configMock->expects($this->once())
            ->method('isSetFlag')
            ->with($pathReportOnly)
            ->willReturn(self::STUB_REPORT_ONLY);

        $this->assertInstanceOf(ModeConfigured::class, $this->model->getConfigured());
    }

    /**
     * Data Provider with appropriate areas.
     *
     * @return array
     */
    public function dataProviderGetConfiguredWithCorrectArea(): array
    {
        return [
            [
                'area' => Area::AREA_ADMINHTML,
                'pathReportOnly' => 'csp/mode/admin/report_only',
                'pathReportUri' => 'csp/mode/admin/report_uri'
            ],
            [
                'area' => Area::AREA_FRONTEND,
                'pathReportOnly' => 'csp/mode/storefront/report_only',
                'pathReportUri' => 'csp/mode/storefront/report_uri'
            ]
        ];
    }

    /**
     * Test case with an inappropriate Area code.
     */
    public function testGetConfiguredWithWrongArea()
    {
        $this->stateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(self::STUB_AREA_CODE_OTHER);

        $this->configMock->expects($this->never())->method('isSetFlag');
        $this->configMock->expects($this->never())->method('getValue');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('CSP can only be configured for storefront or admin area');

        $this->model->getConfigured();
    }
}
