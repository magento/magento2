<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Area;
use Magento\Framework\App\DesignInterface;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\DesignExceptions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AreaTest extends TestCase
{
    const SCOPE_ID = '1';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ConfigLoader|MockObject
     */
    protected $diConfigLoaderMock;

    /**
     * @var TranslateInterface|MockObject
     */
    protected $translatorMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var DesignInterface|MockObject
     */
    protected $designMock;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    protected $scopeResolverMock;

    /**
     * @var DesignExceptions|MockObject
     */
    protected $designExceptionsMock;

    /**
     * @var string
     */
    protected $areaCode;

    /**
     * @var Area
     */
    protected $object;

    /** @var RendererInterface */
    private $defaultRenderer;

    protected function setUp(): void
    {
        $this->defaultRenderer = Phrase::getRenderer();
        $this->objectManager = new ObjectManager($this);
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->translatorMock = $this->getMockBuilder(TranslateInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->diConfigLoaderMock = $this->getMockBuilder(ConfigLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->designMock = $this->getMockBuilder(DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scopeResolverMock = $this->getMockBuilder(ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $scopeMock = $this->getMockBuilder(ScopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $scopeMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::SCOPE_ID);
        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($scopeMock);
        $this->designExceptionsMock = $this->getMockBuilder(DesignExceptions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->areaCode = Area::AREA_FRONTEND;

        $this->object = $this->objectManager->getObject(
            Area::class,
            [
                'logger' => $this->loggerMock,
                'objectManager' => $this->objectManagerMock,
                'eventManager' => $this->eventManagerMock,
                'translator' => $this->translatorMock,
                'diConfigLoader' => $this->diConfigLoaderMock,
                'design' => $this->designMock,
                'scopeResolver' => $this->scopeResolverMock,
                'designExceptions' => $this->designExceptionsMock,
                'areaCode' => $this->areaCode,
            ]
        );
    }

    protected function tearDown(): void
    {
        Phrase::setRenderer($this->defaultRenderer);
    }

    public function testLoadConfig()
    {
        $this->verifyLoadConfig();
        $this->object->load(Area::PART_CONFIG);
    }

    public function testLoadTranslate()
    {
        $this->translatorMock->expects($this->once())
            ->method('loadData');
        $renderMock = $this->getMockBuilder(RendererInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(RendererInterface::class)
            ->willReturn($renderMock);
        $this->object->load(Area::PART_TRANSLATE);
    }

    public function testLoadDesign()
    {
        $designMock = $this->getMockBuilder(\Magento\Framework\View\DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\View\DesignInterface::class)
            ->willReturn($designMock);
        $designMock->expects($this->once())
            ->method('setArea')
            ->with($this->areaCode)
            ->willReturnSelf();
        $designMock->expects($this->once())
            ->method('setDefaultDesignTheme');
        $this->object->load(Area::PART_DESIGN);
    }

    public function testLoadUnknownPart()
    {
        $this->objectManagerMock->expects($this->never())
            ->method('configure');
        $this->objectManagerMock->expects($this->never())
            ->method('get');
        $this->object->load('unknown part');
    }

    public function testLoad()
    {
        $this->verifyLoadConfig();
        $this->translatorMock->expects($this->once())
            ->method('loadData');
        $renderMock = $this->getMockBuilder(RendererInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $designMock = $this->getMockBuilder(\Magento\Framework\View\DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $designMock->expects($this->once())
            ->method('setArea')
            ->with($this->areaCode)
            ->willReturnSelf();
        $designMock->expects($this->once())
            ->method('setDefaultDesignTheme');
        $this->objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                [RendererInterface::class, $renderMock],
                [\Magento\Framework\View\DesignInterface::class, $designMock],
            ]);
        $this->object->load();
    }

    private function verifyLoadConfig()
    {
        $configs = ['dummy configs'];
        $this->diConfigLoaderMock->expects($this->once())
            ->method('load')
            ->with($this->areaCode)
            ->willReturn($configs);
        $this->objectManagerMock->expects($this->once())
            ->method('configure')
            ->with($configs);
    }

    public function testDetectDesign()
    {
        $this->designExceptionsMock->expects($this->never())
            ->method('getThemeByRequest');
        $this->designMock->expects($this->once())
            ->method('loadChange')
            ->with(self::SCOPE_ID)
            ->willReturnSelf();
        $designMock = $this->getMockBuilder(\Magento\Framework\View\DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\View\DesignInterface::class)
            ->willReturn($designMock);
        $this->designMock->expects($this->once())
            ->method('changeDesign')
            ->with($designMock)
            ->willReturnSelf();
        $this->object->detectDesign();
    }

    /**
     * @param string|bool $value
     * @param int $callNum
     * @param int $callNum2
     * @dataProvider detectDesignByRequestDataProvider
     */
    public function testDetectDesignByRequest($value, $callNum, $callNum2)
    {
        $this->designExceptionsMock->expects($this->once())
            ->method('getThemeByRequest')
            ->willReturn($value);
        $designMock = $this->getMockBuilder(\Magento\Framework\View\DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $designMock->expects($this->exactly($callNum))
            ->method('setDesignTheme');
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\View\DesignInterface::class)
            ->willReturn($designMock);
        $this->designMock->expects($this->exactly($callNum2))
            ->method('loadChange')
            ->with(self::SCOPE_ID)
            ->willReturnSelf();
        $this->designMock->expects($this->exactly($callNum2))
            ->method('changeDesign')
            ->with($designMock)
            ->willReturnSelf();
        $requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->object->detectDesign($requestMock);
    }

    /**
     * @return array
     */
    public static function detectDesignByRequestDataProvider()
    {
        return [
            [false, 0, 1],
            ['theme', 1, 0],
        ];
    }

    public function testDetectDesignByRequestWithException()
    {
        $exception = new \Exception('exception');
        $this->designExceptionsMock->expects($this->once())
            ->method('getThemeByRequest')
            ->willThrowException($exception);
        $designMock = $this->getMockBuilder(\Magento\Framework\View\DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $designMock->expects($this->never())
            ->method('setDesignTheme');
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\View\DesignInterface::class)
            ->willReturn($designMock);
        $this->designMock->expects($this->once())
            ->method('loadChange')
            ->with(self::SCOPE_ID)
            ->willReturnSelf();
        $this->designMock->expects($this->once())
            ->method('changeDesign')
            ->with($designMock)
            ->willReturnSelf();
        $requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);
        $this->object->detectDesign($requestMock);
    }
}
