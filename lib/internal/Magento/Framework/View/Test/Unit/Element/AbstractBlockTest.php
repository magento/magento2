<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Framework\Config\View;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\StateInterface as CacheStateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractBlock
     */
    private $block;

    /**
     * @var EventManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var CacheStateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheStateMock;

    /**
     * @var CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var SidResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sidResolverMock;

    /**
     * @var SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->eventManagerMock = $this->getMockForAbstractClass(EventManagerInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->cacheStateMock = $this->getMockForAbstractClass(CacheStateInterface::class);
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->sidResolverMock = $this->getMockForAbstractClass(SidResolverInterface::class);
        $this->sessionMock = $this->getMockForAbstractClass(SessionManagerInterface::class);
        $contextMock = $this->getMock(Context::class, [], [], '', false);
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
            ->method('getCache')
            ->willReturn($this->cacheMock);
        $contextMock->expects($this->once())
            ->method('getSidResolver')
            ->willReturn($this->sidResolverMock);
        $contextMock->expects($this->once())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->block = $this->getMockForAbstractClass(
            AbstractBlock::class,
            ['context' => $contextMock]
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
        $this->block->setNameInLayout($nameInLayout);
        $this->assertEquals($expectedResult, call_user_func_array([$this->block, 'getUiId'], $methodArguments));
    }

    /**
     * @return array
     */
    public function getUiIdDataProvider()
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
        $config = $this->getMock(View::class, ['getVarValue'], [], '', false);
        $module = uniqid();

        $config->expects($this->any())
            ->method('getVarValue')
            ->willReturnMap([
                ['Magento_Theme', 'v1', 'one'],
                [$module, 'v2', 'two']
            ]);

        $configManager = $this->getMock(ConfigInterface::class, [], [], '', false);
        $configManager->expects($this->exactly(2))->method('getViewConfig')->willReturn($config);

        /** @var $block AbstractBlock|\PHPUnit_Framework_MockObject_MockObject */
        $params = ['viewConfig' => $configManager];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $this->assertEquals(AbstractBlock::CACHE_KEY_PREFIX . $cacheKey, $this->block->getCacheKey());
    }

    /**
     * @return void
     */
    public function testGetCacheKeyByName()
    {
        $nameInLayout = 'testBlock';
        $this->block->setNameInLayout($nameInLayout);
        $cacheKey = sha1($nameInLayout);
        $this->assertEquals(AbstractBlock::CACHE_KEY_PREFIX . $cacheKey, $this->block->getCacheKey());
    }

    /**
     * @return void
     */
    public function testToHtmlWhenModuleIsDisabled()
    {
        $moduleName = 'Test';
        $this->block->setData('module_name', $moduleName);

        $this->eventManagerMock->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnMap([
                ['view_block_abstract_to_html_before', ['block' => $this->block]],
                ['view_block_abstract_to_html_after', ['block' => $this->block]],
            ]);

        $this->assertSame('', $this->block->toHtml());
    }

    /**
     * @param string|bool $cacheLifetime
     * @param string|bool $dataFromCache
     * @param string $dataForSaveCache
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectsDispatchEvent
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectsCacheLoad
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectsCacheSave
     * @param string $expectedResult
     * @return void
     * @dataProvider getCacheLifetimeDataProvider
     */
    public function testGetCacheLifetimeViaToHtml(
        $cacheLifetime,
        $dataFromCache,
        $dataForSaveCache,
        $expectsDispatchEvent,
        $expectsCacheLoad,
        $expectsCacheSave,
        $expectedResult
    ) {
        $moduleName = 'Test';
        $cacheKey = 'testKey';
        $this->block->setData('cache_key', $cacheKey);
        $this->block->setData('module_name', $moduleName);
        $this->block->setData('cache_lifetime', $cacheLifetime);

        $this->eventManagerMock->expects($expectsDispatchEvent)
            ->method('dispatch');
        $this->cacheStateMock->expects($this->any())
            ->method('isEnabled')
            ->with(AbstractBlock::CACHE_GROUP)
            ->willReturn(true);
        $this->cacheMock->expects($expectsCacheLoad)
            ->method('load')
            ->with(AbstractBlock::CACHE_KEY_PREFIX . $cacheKey)
            ->willReturn($dataFromCache);
        $this->cacheMock->expects($expectsCacheSave)
            ->method('save')
            ->with($dataForSaveCache, AbstractBlock::CACHE_KEY_PREFIX . $cacheKey);
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
    public function getCacheLifetimeDataProvider()
    {
        return [
            [
                'cacheLifetime' => null,
                'dataFromCache' => 'dataFromCache',
                'dataForSaveCache' => '',
                'expectsDispatchEvent' => $this->exactly(2),
                'expectsCacheLoad' => $this->never(),
                'expectsCacheSave' => $this->never(),
                'expectedResult' => '',
            ],
            [
                'cacheLifetime' => false,
                'dataFromCache' => 'dataFromCache',
                'dataForSaveCache' => '',
                'expectsDispatchEvent' => $this->exactly(2),
                'expectsCacheLoad' => $this->once(),
                'expectsCacheSave' => $this->never(),
                'expectedResult' => 'dataFromCache',
            ],
            [
                'cacheLifetime' => 120,
                'dataFromCache' => 'dataFromCache',
                'dataForSaveCache' => '',
                'expectsDispatchEvent' => $this->exactly(2),
                'expectsCacheLoad' => $this->once(),
                'expectsCacheSave' => $this->never(),
                'expectedResult' => 'dataFromCache',
            ],
            [
                'cacheLifetime' => '120string',
                'dataFromCache' => 'dataFromCache',
                'dataForSaveCache' => '',
                'expectsDispatchEvent' => $this->exactly(2),
                'expectsCacheLoad' => $this->once(),
                'expectsCacheSave' => $this->never(),
                'expectedResult' => 'dataFromCache',
            ],
            [
                'cacheLifetime' => 120,
                'dataFromCache' => false,
                'dataForSaveCache' => '',
                'expectsDispatchEvent' => $this->exactly(2),
                'expectsCacheLoad' => $this->once(),
                'expectsCacheSave' => $this->once(),
                'expectedResult' => '',
            ],
        ];
    }
}
