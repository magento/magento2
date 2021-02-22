<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Model\Layout;

use Magento\Framework\App\State;
use Magento\Framework\Config\Dom\ValidationSchemaException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\LayoutCacheKeyInterface;

/**
 * Class MergeTest
 *
 * @package Magento\Framework\View\Test\Unit\Model\Layout
 */
class MergeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Model\Layout\Merge
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\Url\ScopeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scope;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cache;

    /**
     * @var \Magento\Framework\View\Model\Layout\Update\Validator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $layoutValidator;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializer;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit\Framework\MockObject\MockObject
     */
    private $appState;

    /**
     * @var LayoutCacheKeyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutCacheKeyMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->scope = $this->getMockForAbstractClass(\Magento\Framework\Url\ScopeInterface::class);
        $this->cache = $this->getMockForAbstractClass(\Magento\Framework\Cache\FrontendInterface::class);
        $this->layoutValidator = $this->getMockBuilder(\Magento\Framework\View\Model\Layout\Update\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);
        $this->serializer = $this->getMockForAbstractClass(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutCacheKeyMock = $this->getMockForAbstractClass(LayoutCacheKeyInterface::class);
        $this->layoutCacheKeyMock->expects($this->any())
            ->method('getCacheKeys')
            ->willReturn([]);

        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Model\Layout\Merge::class,
            [
                'scope' => $this->scope,
                'cache' => $this->cache,
                'layoutValidator' => $this->layoutValidator,
                'logger' => $this->logger,
                'appState' => $this->appState,
                'layoutCacheKey' => $this->layoutCacheKeyMock,
                'serializer' => $this->serializer,
            ]
        );
    }

    /**
     */
    public function testValidateMergedLayoutThrowsException()
    {
        $this->expectException(\Magento\Framework\Config\Dom\ValidationSchemaException::class);
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
        $this->cache->expects($this->once())->method('save');

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
