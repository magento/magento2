<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Config\Model\Config\Importer;
use Magento\Config\Model\Config\Importer\SaveProcessor;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Flag;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

/**
 * Test for Importer.
 *
 * @see Importer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImporterTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Importer
     */
    private $model;

    /**
     * @var FlagManager|Mock
     */
    private $flagManagerMock;

    /**
     * @var Flag|Mock
     */
    private $flagMock;

    /**
     * @var ArrayUtils|Mock
     */
    private $arrayUtilsMock;

    /**
     * @var PreparedValueFactory|Mock
     */
    private $valueFactoryMock;

    /**
     * @var ScopeConfigInterface|Mock
     */
    private $scopeConfigMock;

    /**
     * @var State|Mock
     */
    private $stateMock;

    /**
     * @var ScopeInterface|Mock
     */
    private $scopeMock;

    /**
     * @var Value|Mock
     */
    private $valueMock;

    /**
     * @var SaveProcessor|Mock
     */
    private $saveProcessorMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->flagManagerMock = $this->createPartialMockWithReflection(
            FlagManager::class,
            ['getFlagData', 'saveFlag', 'create']
        );
        $this->flagMock = $this->createMock(Flag::class);
        $this->arrayUtilsMock = $this->createMock(ArrayUtils::class);
        $this->valueFactoryMock = $this->createMock(PreparedValueFactory::class);
        $this->valueMock = $this->createMock(Value::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->stateMock = $this->createMock(State::class);
        $this->scopeMock = $this->createMock(ScopeInterface::class);
        $this->saveProcessorMock = $this->createMock(SaveProcessor::class);

        $this->flagManagerMock->expects($this->any())
            ->method('create')
            ->willReturn($this->flagMock);

        $this->model = new Importer(
            $this->flagManagerMock,
            $this->arrayUtilsMock,
            $this->saveProcessorMock,
            $this->scopeConfigMock,
            $this->stateMock,
            $this->scopeMock
        );
    }

    /**
     * @return void
     */
    public function testImport(): void
    {
        $data = [];
        $currentData = ['current' => '2'];

        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(Importer::FLAG_CODE)
            ->willReturn($currentData);
        $this->arrayUtilsMock->expects($this->exactly(2))
            ->method('recursiveDiff')
            ->willReturnMap([
                [$data, $currentData, []],
                [$currentData, $data, []]
            ]);
        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn('oldScope');
        $this->stateMock->expects($this->once())
            ->method('emulateAreaCode')
            ->with(Area::AREA_ADMINHTML, $this->anything())
            ->willReturnCallback(function ($area, $function) {
                $this->assertEquals(Area::AREA_ADMINHTML, $area);
                return $function();
            });
        $this->saveProcessorMock->expects($this->once())
            ->method('process')
            ->with([]);
        $this->scopeMock
            ->method('setCurrentScope')
            ->willReturnCallback(function ($arg1) {
                if ($arg1 == Area::AREA_ADMINHTML || $arg1 == 'oldScope') {
                    return null;
                }
            });
        $this->flagManagerMock->expects($this->once())
            ->method('saveFlag')
            ->with(Importer::FLAG_CODE, $data);

        $this->assertSame(['System config was processed'], $this->model->import($data));
    }

    /**
     * @return void
     */
    public function testImportWithException(): void
    {
        $this->expectException('Magento\Framework\Exception\State\InvalidTransitionException');
        $this->expectExceptionMessage('Some error');
        $data = [];
        $currentData = ['current' => '2'];

        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->willReturn($currentData);
        $this->arrayUtilsMock->expects($this->exactly(2))
            ->method('recursiveDiff')
            ->willReturnMap([
                [$data, $currentData, []],
                [$currentData, $data, []]
            ]);
        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn('oldScope');
        $this->stateMock->expects($this->once())
            ->method('emulateAreaCode')
            ->willThrowException(new \Exception('Some error'));
        $this->scopeMock->expects($this->once())
            ->method('setCurrentScope')
            ->with('oldScope');

        $this->model->import($data);
    }
}
