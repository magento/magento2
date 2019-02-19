<?php
/**
 * Tests Magento\Store\Model\App\Emulation
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model\App;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmulationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\TranslateInterface
     */
    private $translateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Theme\Model\Design
     */
    private $designMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Translate\Inline\ConfigInterface
     */
    private $inlineConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\DesignInterface
     */
    private $viewDesignMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store
     */
    private $storeMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * New store id
     */
    const NEW_STORE_ID = 9;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $model;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        // Mocks
        $this->designMock = $this->getMockBuilder(\Magento\Theme\Model\Design::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $this->translateMock = $this->getMockBuilder(\Magento\Framework\TranslateInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $this->localeResolverMock = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $this->inlineConfigMock = $this->getMockBuilder(\Magento\Framework\Translate\Inline\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $this->inlineTranslationMock = $this->getMockBuilder(\Magento\Framework\Translate\Inline\StateInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $this->viewDesignMock = $this->getMockForAbstractClass(\Magento\Framework\View\DesignInterface::class);
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getStoreId'])
            ->getMock();

        // Stubs
        $this->designMock->expects($this->any())->method('loadChange')->willReturnSelf();
        $this->designMock->expects($this->any())->method('getData')->willReturn(false);

        // Prepare SUT
        $this->model = $this->objectManager->getObject(
            \Magento\Store\Model\App\Emulation::class,
            [
                'storeManager' => $this->storeManagerMock,
                'viewDesign' => $this->viewDesignMock,
                'design' => $this->designMock,
                'translate' => $this->translateMock,
                'scopeConfig' => $this->scopeConfigMock,
                'inlineConfig' => $this->inlineConfigMock,
                'inlineTranslation' => $this->inlineTranslationMock,
                'localeResolver' => $this->localeResolverMock,
            ]
        );
    }

    public function testStartDefaults()
    {
        // Test data
        $inlineTranslate = false;
        $initArea = 'initial area';
        $initTheme = 'initial design theme';
        $initStore = 1;
        $initLocale = 'initial locale code';
        $newInlineTranslate = false;
        $newLocale = 'new locale code';
        $newArea = \Magento\Framework\App\Area::AREA_FRONTEND;

        // Stubs
        $this->inlineTranslationMock->expects($this->any())->method('isEnabled')->willReturn($inlineTranslate);
        $this->viewDesignMock->expects($this->any())->method('getArea')->willReturn($initArea);
        $this->viewDesignMock->expects($this->any())->method('getDesignTheme')->willReturn($initTheme);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getStoreId')->willReturn($initStore);
        $this->localeResolverMock->expects($this->any())->method('getLocale')->willReturn($initLocale);
        $this->inlineConfigMock->expects($this->any())->method('isActive')->willReturn($newInlineTranslate);
        $this->viewDesignMock->expects($this->any())->method('getConfigurationDesignTheme')->willReturn($initTheme);
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn($newLocale);

        // Expectations
        $this->storeMock->expects($this->any())->method('getStoreId')->willReturn($initStore);
        $this->inlineTranslationMock->expects($this->any())->method('suspend')->with($newInlineTranslate);
        $this->viewDesignMock->expects($this->any())->method('setDesignTheme')->with($initTheme);
        $this->localeResolverMock->expects($this->any())->method('setLocale')->with($newLocale);
        $this->translateMock->expects($this->any())->method('setLocale')->with($newLocale);
        $this->translateMock->expects($this->any())->method('loadData')->with($newArea);
        $this->storeManagerMock->expects($this->any())
            ->method('setCurrentStore')->with(self::NEW_STORE_ID);

        // Test
        $result = $this->model->startEnvironmentEmulation(
            self::NEW_STORE_ID,
            \Magento\Framework\App\Area::AREA_FRONTEND
        );
        $this->assertNull($result);
    }

    public function testStop()
    {
        // Test data
        $initArea = 'initial area';
        $initTheme = 'initial design theme';
        $initLocale = 'initial locale code';
        $initialStore = 1;
        $initTranslateInline = false;

        $this->inlineTranslationMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($initTranslateInline);
        $this->viewDesignMock->expects($this->once())
            ->method('getArea')
            ->willReturn($initArea);
        $this->viewDesignMock->expects($this->once())
            ->method('getDesignTheme')
            ->willReturn($initTheme);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getStoreId')->willReturn($initialStore);
        $this->localeResolverMock->expects($this->once())
            ->method('getLocale')
            ->willReturn($initLocale);

        $this->model->storeCurrentEnvironmentInfo();

        // Expectations
        $this->inlineTranslationMock->expects($this->once())
            ->method('resume')
            ->with($initTranslateInline);
        $this->viewDesignMock->expects($this->once())
            ->method('setDesignTheme')
            ->with($initTheme, $initArea);
        $this->storeManagerMock->expects($this->once())
            ->method('setCurrentStore')->with($initialStore);
        $this->localeResolverMock->expects($this->once())
            ->method('setLocale')
            ->with($initLocale);
        $this->translateMock->expects($this->once())
            ->method('setLocale')
            ->with($initLocale);
        $this->translateMock->expects($this->once())->method('loadData')->with($initArea);

        // Test
        $result = $this->model->stopEnvironmentEmulation();
        $this->assertNotNull($result);
    }
}
