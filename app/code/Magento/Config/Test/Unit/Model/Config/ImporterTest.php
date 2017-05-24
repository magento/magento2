<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Config\Model\Config\Importer;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Flag;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\FlagFactory;
use Magento\Framework\Stdlib\ArrayUtils;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test for Importer.
 *
 * @see Importer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Importer
     */
    private $model;

    /**
     * @var FlagFactory|Mock
     */
    private $flagFactoryMock;

    /**
     * @var Flag|Mock
     */
    private $flagMock;

    /**
     * @var FlagResource|Mock
     */
    private $flagResourceMock;

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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->flagFactoryMock = $this->getMockBuilder(FlagFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagMock = $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagResourceMock = $this->getMockBuilder(FlagResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->arrayUtilsMock = $this->getMockBuilder(ArrayUtils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueFactoryMock = $this->getMockBuilder(PreparedValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeMock = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();

        $this->flagFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->flagMock);

        $this->model = new Importer(
            $this->flagFactoryMock,
            $this->flagResourceMock,
            $this->arrayUtilsMock,
            $this->valueFactoryMock,
            $this->scopeConfigMock,
            $this->stateMock,
            $this->scopeMock
        );
    }

    public function testImport()
    {
        $data = [];
        $currentData = ['current' => '2'];

        $reflection = new \ReflectionClass($this->model);
        $closure = $reflection->getMethod('invokeSaveAll')->getClosure($this->model);

        $this->flagResourceMock->expects($this->once())
            ->method('load')
            ->with($this->flagMock, Importer::FLAG_CODE, 'flag_code');
        $this->flagMock->expects($this->once())
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
            ->with(Area::AREA_ADMINHTML, $closure);
        $this->scopeMock->expects($this->once())
            ->method('setCurrentScope')
            ->with('oldScope');
        $this->flagMock->expects($this->once())
            ->method('setFlagData')
            ->with($data);
        $this->flagResourceMock->expects($this->once())
            ->method('save')
            ->with($this->flagMock);

        $this->assertSame(['System config was processed'], $this->model->import($data));
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Some error
     */
    public function testImportWithException()
    {
        $data = [];
        $currentData = ['current' => '2'];

        $this->flagResourceMock->expects($this->once())
            ->method('load')
            ->with($this->flagMock, Importer::FLAG_CODE, 'flag_code');
        $this->flagMock->expects($this->once())
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

    public function testInvokeSaveAll()
    {
        $reflection = new \ReflectionClass($this->model);
        $closure = $reflection->getMethod('invokeSaveAll')->getClosure($this->model);
        $data = [
            'default' => ['web' => ['unsecure' => ['base_url' => 'http://magento2.local/']]],
            'websites' => ['base' => ['web' => ['unsecure' => ['base_url' => 'http://magento2.local/']]]],
        ];

        $this->valueMock->expects($this->exactly(2))
            ->method('beforeSave');
        $this->valueMock->expects($this->exactly(2))
            ->method('afterSave');

        $value1 = clone $this->valueMock;
        $value2 = clone $this->valueMock;

        $this->arrayUtilsMock->expects($this->exactly(2))
            ->method('flatten')
            ->willReturnMap([
                [
                    ['web' => ['unsecure' => ['base_url' => 'http://magento2.local/']]],
                    '',
                    '/',
                    ['web/unsecure/base_url' => 'http://magento2.local/']
                ],
                [
                    ['base' => ['web' => ['unsecure' => ['base_url' => 'http://magento23.local/']]]],
                    '',
                    '/',
                    ['base/web/unsecure/base_url' => 'http://magento23.local/']
                ]
            ]);
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap([
                ['web/unsecure/base_url', 'default', null, 'http://magento2.local/'],
                ['web/unsecure/base_url', 'websites', 'base', 'http://magento23.local/']
            ]);
        $this->valueFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                ['web/unsecure/base_url', 'http://magento2.local/', 'default', null, $value1],
                ['web/unsecure/base_url', 'http://magento23.local/', 'websites', 'base', $value2]
            ]);

        $this->assertSame(null, $closure($data));
    }
}
