<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

use \Magento\Framework\App\Area;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AreaTest extends \PHPUnit_Framework_TestCase
{
    const SCOPE_ID = '1';

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $diConfigLoaderMock;

    /**
     * @var \Magento\Framework\TranslateInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translatorMock;

    /**
     * @var \Psr\Log\LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\App\DesignInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $designMock;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeResolverMock;

    /**
     * @var \Magento\Framework\View\DesignExceptions | \PHPUnit_Framework_MockObject_MockObject
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

    /** @var \Magento\Framework\Phrase\RendererInterface */
    private $defaultRenderer;

    protected function setUp()
    {
        $this->defaultRenderer = \Magento\Framework\Phrase::getRenderer();
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->translatorMock = $this->getMockBuilder('Magento\Framework\TranslateInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->diConfigLoaderMock = $this->getMockBuilder('Magento\Framework\App\ObjectManager\ConfigLoader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->designMock = $this->getMockBuilder('Magento\Framework\App\DesignInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeResolverMock = $this->getMockBuilder('Magento\Framework\App\ScopeResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $scopeMock = $this->getMockBuilder('Magento\Framework\App\ScopeInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $scopeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::SCOPE_ID));
        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->will($this->returnValue($scopeMock));
        $this->designExceptionsMock = $this->getMockBuilder('Magento\Framework\View\DesignExceptions')
            ->disableOriginalConstructor()
            ->getMock();
        $this->areaCode = Area::AREA_FRONTEND;

        $this->object = $this->objectManager->getObject(
            'Magento\Framework\App\Area',
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

    public function tearDown()
    {
        \Magento\Framework\Phrase::setRenderer($this->defaultRenderer);
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
        $renderMock = $this->getMockBuilder('Magento\Framework\Phrase\RendererInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Phrase\RendererInterface')
            ->will($this->returnValue($renderMock));
        $this->object->load(Area::PART_TRANSLATE);
    }

    public function testLoadDesign()
    {
        $designMock = $this->getMockBuilder('Magento\Framework\View\DesignInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\View\DesignInterface')
            ->will($this->returnValue($designMock));
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
        $renderMock = $this->getMockBuilder('Magento\Framework\Phrase\RendererInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $designMock = $this->getMockBuilder('Magento\Framework\View\DesignInterface')
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
            ->will($this->returnValueMap(
                [
                    ['Magento\Framework\Phrase\RendererInterface', $renderMock],
                    ['Magento\Framework\View\DesignInterface', $designMock],
                ]
            ));
        $this->object->load();
    }

    private function verifyLoadConfig()
    {
        $configs = ['dummy configs'];
        $this->diConfigLoaderMock->expects($this->once())
            ->method('load')
            ->with($this->areaCode)
            ->will($this->returnValue($configs));
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
        $designMock = $this->getMockBuilder('Magento\Framework\View\DesignInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\View\DesignInterface')
            ->will($this->returnValue($designMock));
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
            ->will($this->returnValue($value));
        $designMock = $this->getMockBuilder('Magento\Framework\View\DesignInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $designMock->expects($this->exactly($callNum))
            ->method('setDesignTheme');
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\View\DesignInterface')
            ->will($this->returnValue($designMock));
        $this->designMock->expects($this->exactly($callNum2))
            ->method('loadChange')
            ->with(self::SCOPE_ID)
            ->willReturnSelf();
        $this->designMock->expects($this->exactly($callNum2))
            ->method('changeDesign')
            ->with($designMock)
            ->willReturnSelf();
        $requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->object->detectDesign($requestMock);
    }

    public function detectDesignByRequestDataProvider()
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
            ->will($this->throwException($exception));
        $designMock = $this->getMockBuilder('Magento\Framework\View\DesignInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $designMock->expects($this->never())
            ->method('setDesignTheme');
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\View\DesignInterface')
            ->will($this->returnValue($designMock));
        $this->designMock->expects($this->once())
            ->method('loadChange')
            ->with(self::SCOPE_ID)
            ->willReturnSelf();
        $this->designMock->expects($this->once())
            ->method('changeDesign')
            ->with($designMock)
            ->willReturnSelf();
        $requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);
        $this->object->detectDesign($requestMock);
    }
}
