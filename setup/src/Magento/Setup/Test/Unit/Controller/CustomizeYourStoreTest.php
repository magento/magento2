<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Setup\Lists;
use Magento\Framework\Setup\SampleData\State;
use Magento\Setup\Controller\CustomizeYourStore;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomizeYourStoreTest extends TestCase
{
    /**
     * @var CustomizeYourStore
     */
    private $controller;

    /**
     * @var MockObject|State
     */
    private $sampleDataState;

    /**
     * @var MockObject|Lists
     */
    private $lists;

    /**
     * @var MockObject|ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject|FullModuleList
     */
    private $moduleList;

    protected function setup(): void
    {
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->sampleDataState = $this->createMock(State::class);
        $this->lists = $this->createMock(Lists::class);
        $this->moduleList = $this->createMock(FullModuleList::class);
        $this->controller = new CustomizeYourStore($this->moduleList, $this->lists, $objectManagerProvider);
    }

    /**
     * @param array $expected
     * @param $withSampleData
     *
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($expected, $withSampleData)
    {
        if ($withSampleData) {
            $this->moduleList->expects($this->once())->method('has')->willReturn(true);
            $this->objectManager->expects($this->once())->method('get')->willReturn($this->sampleDataState);
            $this->sampleDataState->expects($this->once())->method('isInstalled')
                ->willReturn($expected['isSampleDataInstalled']);
            $this->sampleDataState->expects($this->once())->method('hasError')
                ->willReturn($expected['isSampleDataErrorInstallation']);
        } else {
            $this->moduleList->expects($this->once())->method('has')->willReturn(false);
            $this->objectManager->expects($this->never())->method('get');
        }
        $this->lists->expects($this->once())->method('getTimezoneList')->willReturn($expected['timezone']);
        $this->lists->expects($this->once())->method('getCurrencyList')->willReturn($expected['currency']);
        $this->lists->expects($this->once())->method('getLocaleList')->willReturn($expected['language']);

        $viewModel = $this->controller->indexAction();

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());

        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('timezone', $variables);
        $this->assertArrayHasKey('currency', $variables);
        $this->assertArrayHasKey('language', $variables);
        $this->assertSame($expected, $variables);
    }

    /**
     * @return array
     */
    public function indexActionDataProvider()
    {
        $timezones = ['timezone' => ['America/New_York'=>'EST', 'America/Chicago' => 'CST']];
        $currency = ['currency' => ['USD'=>'US Dollar', 'EUR' => 'Euro']];
        $language = ['language' => ['en_US'=>'English (USA)', 'en_UK' => 'English (UK)']];
        $sampleData = [
            'isSampleDataInstalled' => false,
            'isSampleDataErrorInstallation' => false
        ];

        return [
            'with_all_data' => [array_merge($timezones, $currency, $language, $sampleData), true],
            'no_currency_data' => [array_merge($timezones, ['currency' => null], $language, $sampleData), true],
            'no_timezone_data' => [array_merge(['timezone' => null], $currency, $language, $sampleData), true],
            'no_language_data' => [array_merge($timezones, $currency, ['language' => null], $sampleData), true],
            'empty_currency_data' => [array_merge($timezones, ['currency' => []], $language, $sampleData), true],
            'empty_timezone_data' => [array_merge(['timezone' => []], $currency, $language, $sampleData), true],
            'empty_language_data' => [array_merge($timezones, $currency, ['language' => []], $sampleData), true],
            'no_sample_data' => [array_merge($timezones, $currency, $language, $sampleData), false],
        ];
    }

    public function testDefaultTimeZoneAction()
    {
        $jsonModel = $this->controller->defaultTimeZoneAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $this->assertArrayHasKey('defaultTimeZone', $jsonModel->getVariables());
    }
}
