<?php
/**
 * Tests Magento\Core\Model\App\Emulation
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\App;

class EmulationTest extends \Magento\Test\BaseTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\StoreManagerInterface
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\Design
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
     * @var \Magento\Core\Model\App\Emulation
     */
    private $model;

    public function setUp()
    {
        parent::setUp();
        // Mocks
        $this->designMock = $this->basicMock('Magento\Core\Model\Design');
        $this->storeManagerMock = $this->basicMock('Magento\Framework\StoreManagerInterface');
        $this->translateMock = $this->basicMock('Magento\Framework\TranslateInterface');
        $this->scopeConfigMock = $this->basicMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->localeResolverMock = $this->basicMock('Magento\Framework\Locale\ResolverInterface');
        $this->inlineConfigMock = $this->basicMock('Magento\Framework\Translate\Inline\ConfigInterface');
        $this->inlineTranslationMock = $this->basicMock('Magento\Framework\Translate\Inline\StateInterface');
        $this->viewDesignMock = $this->getMockForAbstractClass('Magento\Framework\View\DesignInterface');

        // Stubs
        $this->designMock->expects($this->any())->method('loadChange')->willReturnSelf();
        $this->designMock->expects($this->any())->method('getData')->willReturn(false);
        $this->translateMock->expects($this->any())->method('setLocale')->willReturnSelf();

        // Prepare SUT
        $this->model = $this->objectManager->getObject('Magento\Core\Model\App\Emulation',
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
        $newDesignData = ['array', 'with', 'data'];
        $inlineTranslate = false;
        $initArea = 'initial area';
        $initTheme = 'initial design theme';
        $initStore = 1;
        $initLocale = 'initial locale code';
        $newInlineTranslate = false;
        $newLocale = 'new locale code';
        $newStoreId = 9;
        $initDesignData = ['area' => $initArea, 'theme' => $initTheme, 'store' => $initStore];

        // Stubs

        $this->inlineTranslationMock->expects($this->any())->method('isEnabled')->willReturn($inlineTranslate);
        $this->viewDesignMock->expects($this->any())->method('getArea')->willReturn($initArea);
        $this->viewDesignMock->expects($this->any())->method('getDesignTheme')->willReturn($initTheme);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($initStore);
        $this->localeResolverMock->expects($this->any())->method('getLocaleCode')->willReturn($initLocale);
        $this->inlineConfigMock->expects($this->any())->method('isActive')->willReturn($newInlineTranslate);
        $this->viewDesignMock->expects($this->any())->method('getConfigurationDesignTheme')->willReturn($newDesignData);
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn($newLocale);

        // Expectations
        $this->inlineTranslationMock->expects($this->once())->method('suspend')->with($newInlineTranslate);
        $this->viewDesignMock->expects($this->once())->method('setDesignTheme')->with($newDesignData);
        $this->localeResolverMock->expects($this->once())->method('setLocaleCode')->with($newLocale);
        $this->translateMock->expects($this->once())->method('setLocale')->with($newLocale);
        $this->storeManagerMock->expects($this->once())->method('setCurrentStore')->with($newStoreId);

        // Test
        $initialEnvironment = $this->model->startEnvironmentEmulation($newStoreId);
        $this->assertSame($inlineTranslate, $initialEnvironment->getInitialTranslateInline());
        $this->assertSame($initDesignData, $initialEnvironment->getInitialDesign());
        $this->assertSame($initLocale, $initialEnvironment->getInitialLocaleCode());
    }

    public function testStartWithInlineTranslation()
    {
        $inlineTranslation = true;

        $this->inlineConfigMock->expects($this->any())->method('isActive')->willReturn($inlineTranslation);

        $this->inlineTranslationMock->expects($this->once())
            ->method('suspend')
            ->with($inlineTranslation);

        $this->model->startEnvironmentEmulation(1, null, true);

    }

    public function testStartAreaNotDefault()
    {
        $area = 'backend';
        $newDesignData = ['array', 'with', 'data'];

        $this->viewDesignMock->expects($this->any())->method('getConfiguratioNDesignTheme')->willReturn($newDesignData);

        $this->viewDesignMock->expects($this->once())
            ->method('setDesignTheme')
            ->with($newDesignData, $area);

        $this->model->startEnvironmentEmulation(1, $area);
    }

    public function testStop()
    {
        // Test data
        $initialEnvInfo = $this->objectManager->getObject('\Magento\Framework\Object');
        $initArea = 'initial area';
        $initTheme = 'initial design theme';
        $initStore = 1;
        $initLocale = 'initial locale code';
        $initTranslateInline = false;
        $initDesignData = ['area' => $initArea, 'theme' => $initTheme, 'store' => $initStore];
        $initialEnvInfo->setInitialTranslateInline($initTranslateInline)
            ->setInitialDesign($initDesignData)
            ->setInitialLocaleCode($initLocale);

        // Expectations
        $this->inlineTranslationMock->expects($this->once())
            ->method('resume')
            ->with($initTranslateInline);
        $this->viewDesignMock->expects($this->once())
            ->method('setDesignTheme')
            ->with($initTheme, $initArea);
        $this->storeManagerMock->expects($this->once())
            ->method('setCurrentStore')
            ->with($initStore);
        $this->localeResolverMock->expects($this->once())
            ->method('setLocaleCode')
            ->with($initLocale);
        $this->translateMock->expects($this->once())
            ->method('setLocale')
            ->with($initLocale);

        // Test
        $this->assertSame($this->model, $this->model->stopEnvironmentEmulation($initialEnvInfo));
    }
} 