<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Model\Layout;

use Magento\Framework\App\State;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Config\Dom\ValidationSchemaException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\ScopeInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Layout\LayoutCacheKeyInterface;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Framework\View\Model\Layout\Update\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MergeTest extends TestCase
{
    /**
     * @var Merge
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var ScopeInterface|MockObject
     */
    private $scope;

    /**
     * @var FrontendInterface|MockObject
     */
    private $cache;

    /**
     * @var Validator|MockObject
     */
    private $layoutValidator;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var State|MockObject
     */
    private $appState;

    /**
     * @var LayoutCacheKeyInterface|MockObject
     */
    protected $layoutCacheKeyMock;
    /**
     * @var ThemeInterface|MockObject
     */
    private $theme;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->scope = $this->getMockForAbstractClass(ScopeInterface::class);
        $this->cache = $this->getMockForAbstractClass(FrontendInterface::class);
        $this->layoutValidator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->serializer = $this->getMockForAbstractClass(SerializerInterface::class);
        $this->appState = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutCacheKeyMock = $this->getMockForAbstractClass(LayoutCacheKeyInterface::class);
        $this->layoutCacheKeyMock->expects($this->any())
            ->method('getCacheKeys')
            ->willReturn([]);

        $this->theme = $this->createMock(ThemeInterface::class);

        $this->model = $this->objectManagerHelper->getObject(
            Merge::class,
            [
                'scope' => $this->scope,
                'cache' => $this->cache,
                'layoutValidator' => $this->layoutValidator,
                'logger' => $this->logger,
                'appState' => $this->appState,
                'layoutCacheKey' => $this->layoutCacheKeyMock,
                'serializer' => $this->serializer,
                'theme' => $this->theme,
            ]
        );
    }

    public function testValidateMergedLayoutThrowsException()
    {
        $this->expectException('Magento\Framework\Config\Dom\ValidationSchemaException');
        $this->expectExceptionMessage('Processed schema file is not valid.');
        $messages = [
            'Please correct the XSD data and try again.',
        ];
        $this->scope->expects($this->once())->method('getId')->willReturn(1);
        $this->layoutValidator->expects($this->once())
            ->method('isValid')
            ->willThrowException(
                new ValidationSchemaException(
                    new Phrase('Processed schema file is not valid.')
                )
            );
        $this->layoutValidator->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);
        $this->appState->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->model->load();
    }

    /**
     * Test that merged layout is saved to cache if it wasn't cached before.
     */
    public function testSaveToCache()
    {
        $this->scope->expects($this->once())->method('getId')->willReturn(1);
        $this->theme->method('getArea')->willReturn('frontend');
        $this->theme->method('getId')->willReturn(1);
        $cacheKey = 'LAYOUT_frontend_STORE1_1d41d8cd98f00b204e9800998ecf8427e_page_layout_merged';
        $this->cache->expects($this->once())
            ->method('save')
            ->with(null, $cacheKey, [], 31536000);

        $this->model->load();
    }

    /**
     * Test that merged layout is not re-saved to cache when it was loaded from cache.
     */
    public function testNoSaveToCacheWhenCachePresent()
    {
        $cacheValue = [
            "pageLayout" => "1column",
            "layout"     => "<body></body>"
        ];

        $this->scope->expects($this->once())->method('getId')->willReturn(1);
        $this->cache->expects($this->once())->method('load')->willReturn(json_encode($cacheValue));
        $this->serializer->expects($this->once())->method('unserialize')->willReturn($cacheValue);
        $this->cache->expects($this->never())->method('save');

        $this->model->load();
    }
}
