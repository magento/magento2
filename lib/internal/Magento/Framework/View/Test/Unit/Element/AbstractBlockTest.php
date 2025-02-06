<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\App\Cache\StateInterface as CacheStateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Cache\LockGuardedCacheLoader;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\View;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractBlockTest extends TestCase
{
    /**
     * @var AbstractBlock
     */
    private $block;

    /**
     * @var EventManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var CacheStateInterface|MockObject
     */
    private $cacheStateMock;

    /**
     * @var SidResolverInterface|MockObject
     */
    private $sidResolverMock;

    /**
     * @var SessionManagerInterface|MockObject
     */
    private $sessionMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var LockGuardedCacheLoader|MockObject
     */
    private $lockQuery;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();
        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerMock);
        $this->eventManagerMock = $this->getMockForAbstractClass(EventManagerInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->cacheStateMock = $this->getMockForAbstractClass(CacheStateInterface::class);
        $this->lockQuery = $this->getMockBuilder(LockGuardedCacheLoader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['lockedLoadData'])
            ->getMockForAbstractClass();
        $this->sidResolverMock = $this->getMockForAbstractClass(SidResolverInterface::class);
        $this->sessionMock = $this->getMockForAbstractClass(SessionManagerInterface::class);
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);
        $contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        $contextMock->expects($this->once())
            ->method('getCacheState')
            ->willReturn($this->cacheStateMock);
        $contextMock->expects($this->once())
            ->method('getSidResolver')
            ->willReturn($this->sidResolverMock);
        $contextMock->expects($this->once())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $contextMock->expects($this->once())
            ->method('getEscaper')
            ->willReturn($this->escaperMock);
        $contextMock->expects($this->once())
            ->method('getLockGuardedCacheLoader')
            ->willReturn($this->lockQuery);

        $this->block = $this->getMockForAbstractClass(
            AbstractBlock::class,
            [
                'context' => $contextMock,
                'data' => []
            ]
        );
        $this->deploymentConfig = $this->createPartialMock(
            DeploymentConfig::class,
            ['get']
        );
    }

    /**
     * @param string $expectedResult
     * @param string $nameInLayout
     * @param array $methodArguments
     * @dataProvider getUiIdDataProvider
     */
    public function testGetUiId($expectedResult, $nameInLayout, $methodArguments)
    {
        $this->escaperMock->expects($this->once())
            ->method('escapeHtmlAttr')
            ->willReturnCallback(
                function ($string) {
                    return $string;
                }
            );
        $this->block->setNameInLayout($nameInLayout);
        $this->assertEquals($expectedResult, call_user_func_array([$this->block, 'getUiId'], $methodArguments));
    }

    /**
     * @return array
     */
    public static function getUiIdDataProvider()
    {
        return [
            [' data-ui-id="" ', null, []],
            [' data-ui-id="block" ', 'block', []],
            [' data-ui-id="block" ', 'block---', []],
            [' data-ui-id="block" ', '--block', []],
            [' data-ui-id="bl-ock" ', '--bl--ock---', []],
            [' data-ui-id="bl-ock" ', '--bL--Ock---', []],
            [' data-ui-id="b-l-o-c-k" ', '--b!@#$%^&**()L--O;:...c<_>k---', []],
            [
                ' data-ui-id="a0b1c2d3e4f5g6h7-i8-j9k0l1m2n-3o4p5q6r7-s8t9u0v1w2z3y4x5" ',
                'a0b1c2d3e4f5g6h7',
                ['i8-j9k0l1m2n-3o4p5q6r7', 's8t9u0v1w2z3y4x5']
            ],
            [
                ' data-ui-id="capsed-block-name-cap-ed-param1-caps2-but-ton" ',
                'CaPSed BLOCK NAME',
                ['cAp$Ed PaRaM1', 'caPs2', 'bUT-TOn']
            ],
            [
                ' data-ui-id="capsed-block-name-cap-ed-param1-caps2-but-ton-but-ton" ',
                'CaPSed BLOCK NAME',
                ['cAp$Ed PaRaM1', 'caPs2', 'bUT-TOn', 'bUT-TOn']
            ],
            [' data-ui-id="block-0-1-2-3-4" ', '!block!', range(0, 5)]
        ];
    }

    /**
     * @return void
     */
    public function testGetVar()
    {
        $config = $this->createPartialMock(View::class, ['getVarValue']);
        $module = uniqid();

        $config->expects($this->any())
            ->method('getVarValue')
            ->willReturnMap(
                [
                    ['Magento_Theme', 'v1', 'one'],
                    [$module, 'v2', 'two']
                ]
            );

        $configManager = $this->getMockForAbstractClass(ConfigInterface::class);
        $configManager->expects($this->exactly(2))->method('getViewConfig')->willReturn($config);

        /** @var AbstractBlock|MockObject $block */
        $params = ['viewConfig' => $configManager];
        $helper = new ObjectManager($this);
        $block = $this->getMockForAbstractClass(
            AbstractBlock::class,
            $helper->getConstructArguments(AbstractBlock::class, $params)
        );
        $block->setData('module_name', 'Magento_Theme');

        $this->assertEquals('one', $block->getVar('v1'));
        $this->assertEquals('two', $block->getVar('v2', $module));
    }

    /**
     * @return void
     */
    public function testIsScopePrivate()
    {
        $this->assertFalse($this->block->isScopePrivate());
    }

    /**
     * @return void
     */
    public function testGetCacheKey()
    {
        $cacheKey = 'testKey';
        $this->block->setData('cache_key', $cacheKey);
        $this->assertEquals(AbstractBlock::CUSTOM_CACHE_KEY_PREFIX . $cacheKey, $this->block->getCacheKey());
    }

    /**
     * Test for invalid cacheKey name
     * @return void
     * @throws RuntimeException
     */
    public function testGetCacheKeyFail(): void
    {
        $cacheKey = "test&''Key";
        $this->block->setData('cache_key', $cacheKey);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage((string)__('Please enter cache key with only alphanumeric or hash string.'));
        $this->block->getCacheKey();
    }

    /**
     * @return void
     */
    public function testGetCacheKeyByName()
    {
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(DeploymentConfig::class)
            ->willReturn($this->deploymentConfig);

        $this->deploymentConfig->expects($this->any())
            ->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY)
            ->willReturn('448198e08af35844a42d3c93c1ef4e03');

        $nameInLayout = 'testBlock';
        $this->block->setNameInLayout($nameInLayout);
        $encryptionKey = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        $cacheKey = hash('sha256', $nameInLayout . '|' . $encryptionKey);
        $this->assertEquals(AbstractBlock::CACHE_KEY_PREFIX . $cacheKey, $this->block->getCacheKey());
    }

    /**
     * @return void
     */
    public function testToHtmlWhenModuleIsDisabled()
    {
        $moduleName = 'Test';
        $this->block->setData('module_name', $moduleName);

        $this->eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->with('view_block_abstract_to_html_before', ['block' => $this->block]);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('advanced/modules_disable_output/' . $moduleName, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $this->assertSame('', $this->block->toHtml());
    }

    /**
     * @param string|bool $cacheLifetime
     * @param string|bool $dataFromCache
     * @param InvokedCount $expectsDispatchEvent
     * @param string $expectedResult
     * @return void
     * @dataProvider getCacheLifetimeDataProvider
     */
    public function testGetCacheLifetimeViaToHtml(
        $cacheLifetime,
        $dataFromCache,
        $expectsDispatchEvent,
        $expectedResult
    ) {
        $moduleName = 'Test';
        $cacheKey = 'testKey';
        $this->block->setData('cache_key', $cacheKey);
        $this->block->setData('module_name', $moduleName);
        $this->block->setData('cache_lifetime', $cacheLifetime);

        $this->eventManagerMock->expects($expectsDispatchEvent)
            ->method('dispatch');
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('advanced/modules_disable_output/' . $moduleName, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);
        $this->cacheStateMock->expects($this->any())
            ->method('isEnabled')
            ->with(AbstractBlock::CACHE_GROUP)
            ->willReturn(true);
        $this->lockQuery->expects($this->any())
            ->method('lockedLoadData')
            ->willReturn($dataFromCache);
        $this->sidResolverMock->expects($this->any())
            ->method('getSessionIdQueryParam')
            ->with($this->sessionMock)
            ->willReturn('sessionIdQueryParam');
        $this->sessionMock->expects($this->any())
            ->method('getSessionId')
            ->willReturn('sessionId');

        $this->assertSame($expectedResult, $this->block->toHtml());
    }

    /**
     * @return array
     */
    public static function getCacheLifetimeDataProvider()
    {
        return [
            [
                'cacheLifetime' => null,
                'dataFromCache' => 'dataFromCache',
                'expectsDispatchEvent' => self::exactly(2),
                'expectedResult' => '',
            ],
            [
                'cacheLifetime' => false,
                'dataFromCache' => 'dataFromCache',
                'expectsDispatchEvent' => self::exactly(2),
                'expectedResult' => '',
            ],
            [
                'cacheLifetime' => 120,
                'dataFromCache' => 'dataFromCache',
                'expectsDispatchEvent' => self::exactly(2),
                'expectedResult' => 'dataFromCache',
            ],
            [
                'cacheLifetime' => '120string',
                'dataFromCache' => 'dataFromCache',
                'expectsDispatchEvent' => self::exactly(2),
                'expectedResult' => 'dataFromCache',
            ],
            [
                'cacheLifetime' => 120,
                'dataFromCache' => false,
                'expectsDispatchEvent' => self::exactly(2),
                'expectedResult' => '',
            ],
        ];
    }

    /**
     * @return void
     */
    public function testExtractModuleName()
    {
        $blockClassNames = $this->getPossibleBlockClassNames();

        foreach ($blockClassNames as $expectedModuleName => $className) {
            $extractedModuleName = $this->block->extractModuleName($className);
            $this->assertSame($expectedModuleName, $extractedModuleName);
        }
    }

    /**
     * @return array
     */
    private function getPossibleBlockClassNames()
    {
        return [
            'Vendor_Module' => 'Vendor\Module\Block\Class',
            'Vendor_ModuleBlock' => 'Vendor\ModuleBlock\Block\Class',
            'Vendor_BlockModule' => 'Vendor\BlockModule\Block\Class',
            'Vendor_CustomBlockModule' => 'Vendor\CustomBlockModule\Block\Class',
        ];
    }
}
