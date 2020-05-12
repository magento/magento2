<?php declare(strict_types=1);
/**
 * Tests Magento\Store\Model\App\Emulation
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model\App;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\Inline\ConfigInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Design;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmulationTest extends TestCase
{
    private const STUB_NEW_STORE_ID = 9;

    /**
     * @var MockObject|StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var MockObject|TranslateInterface
     */
    private $translateMock;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var MockObject|ResolverInterface
     */
    private $localeResolverMock;

    /**
     * @var MockObject|Design
     */
    private $designMock;

    /**
     * @var MockObject|ConfigInterface
     */
    private $inlineConfigMock;

    /**
     * @var MockObject|StateInterface
     */
    private $inlineTranslationMock;

    /**
     * @var MockObject|DesignInterface
     */
    private $viewDesignMock;

    /**
     * @var MockObject|Store
     */
    private $storeMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Emulation
     */
    private $model;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        // Mocks
        $this->designMock = $this->getMockBuilder(Design::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMockForAbstractClass();
        $this->translateMock = $this->getMockBuilder(TranslateInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMockForAbstractClass();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMockForAbstractClass();
        $this->localeResolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMockForAbstractClass();
        $this->inlineConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMockForAbstractClass();
        $this->inlineTranslationMock = $this->getMockBuilder(StateInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMockForAbstractClass();
        $this->viewDesignMock = $this->getMockForAbstractClass(DesignInterface::class);
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getStoreId'])
            ->getMock();

        // Stubs
        $this->designMock->expects($this->any())->method('loadChange')->willReturnSelf();
        $this->designMock->expects($this->any())->method('getData')->willReturn(false);

        // Prepare SUT
        $this->model = $this->objectManager->getObject(
            Emulation::class,
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
        $newArea = Area::AREA_FRONTEND;

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
            ->method('setCurrentStore')->with(self::STUB_NEW_STORE_ID);

        // Test
        $result = $this->model->startEnvironmentEmulation(
            self::STUB_NEW_STORE_ID,
            Area::AREA_FRONTEND
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
