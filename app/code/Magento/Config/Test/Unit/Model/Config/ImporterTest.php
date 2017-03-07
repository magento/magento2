<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Config\Model\Config\Importer;
use Magento\Config\Model\ValueBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
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
     * @var ValueBuilder|Mock
     */
    private $valueBuilderMock;

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
        $this->valueBuilderMock = $this->getMockBuilder(ValueBuilder::class)
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
            $this->valueBuilderMock,
            $this->scopeConfigMock,
            $this->stateMock,
            $this->scopeMock
        );
    }

    public function testImport()
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

        $this->model->import($data);
    }
}
