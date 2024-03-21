<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Bookmark;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterface;
use Magento\Ui\Controller\Adminhtml\Bookmark\Save;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Bookmark Save controller test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var MockObject|Context
     */
    private $context;

    /**
     * @var MockObject|UiComponentFactory
     */
    private $factory;

    /**
     * @var MockObject|BookmarkRepositoryInterface
     */
    private $bookmarkRepository;

    /**
     * @var MockObject|BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @var MockObject|BookmarkInterfaceFactory
     */
    private $bookmarkFactory;

    /**
     * @var MockObject|UserContextInterface
     */
    private $userContext;

    /**
     * @var MockObject|DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var MockObject|Json
     */
    private $serializer;

    /**
     * @var Save
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->factory = $this->createMock(UiComponentFactory::class);
        $this->bookmarkRepository = $this->createMock(BookmarkRepositoryInterface::class);
        $this->bookmarkManagement = $this->createMock(BookmarkManagementInterface::class);
        $this->bookmarkFactory = $this->createMock(BookmarkInterfaceFactory::class);
        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->jsonDecoder = $this->createMock(DecoderInterface::class);
        $this->serializer = $this->createMock(Json::class);

        $this->model = new Save(
            $this->context,
            $this->factory,
            $this->bookmarkRepository,
            $this->bookmarkManagement,
            $this->bookmarkFactory,
            $this->userContext,
            $this->jsonDecoder,
            $this->serializer
        );
    }

    /**
     * Tests execute method.
     * Test when User Context doesn't provide userId. In such a case the method should not be executed.
     *
     * @return void
     */
    public function testExecuteWontBeExecutedWhenNoUserIdInContext(): void
    {
        $this->factory->expects($this->never())
            ->method($this->anything());
        $this->bookmarkRepository->expects($this->never())
            ->method($this->anything());
        $this->bookmarkManagement->expects($this->never())
            ->method($this->anything());
        $this->bookmarkFactory->expects($this->never())
            ->method($this->anything());
        $this->jsonDecoder->expects($this->never())
            ->method($this->anything());

        $this->userContext->method('getUserId')
            ->willReturn(null);

        $this->model->execute();
    }

    /**
     * Tests that on bookmark switch the previous active bookmark is not any more set as "current"
     * And that the new selected bookmark is now set as "current"
     *
     * @return void
     * @throws LocalizedException
     * @throws \ReflectionException
     */
    public function testExecuteForCurrentBookmarkUpdate() : void
    {
        $currentConfig = '{"activeIndex":"bookmark2"}';
        $updatedConfig = '{"current":' . json_encode($this->getConfigData('P2', 1, 2)) . '}';

        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $bookmark = $this->createMock(BookmarkInterface::class);
        $this->bookmarkFactory->expects($this->once())->method('create')->willReturn($bookmark);

        $this->serializer->expects($this->once())->method('unserialize')->with($currentConfig)
            ->willReturn(json_decode($currentConfig, true));

        $request = $this->getMockForAbstractClass(RequestInterface::class);
        $request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['data'] => '{"' . Save::ACTIVE_IDENTIFIER . '":"bookmark2"}',
                ['namespace'] => 'product_listing'
            });
        $reflectionProperty = new \ReflectionProperty($this->model, '_request');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $request);

        $current = $this->createBookmark();
        $bookmark1 = $this->createBookmark('bookmark1', '1', $this->getConfigData('P1', 1, 2));
        $bookmark2 = $this->createBookmark('bookmark2', '0', $this->getConfigData('P2', 1, 2));

        $searchResult = $this->createMock(BookmarkSearchResultsInterface::class);
        $searchResult->expects($this->exactly(2))
            ->method('getItems')
            ->willReturn([$current, $bookmark1, $bookmark2]);
        $this->bookmarkManagement->expects($this->once())->method('loadByNamespace')->willReturn($searchResult);
        $bookmark1->expects($this->once())->method('getIdentifier')->willReturn('bookmark1');
        $bookmark1->expects($this->once())->method('setCurrent')->with(false);

        $bookmark2->expects($this->exactly(2))->method('getIdentifier')->willReturn('bookmark2');
        $bookmark2->expects($this->once())->method('getConfig')->willReturnSelf();
        $bookmark2->expects($this->once())->method('setCurrent')->with(true);

        $current->expects($this->exactly(2))->method('getIdentifier')->willReturn('current');
        $current->expects($this->once())->method('setCurrent')->with(false);
        $current->expects($this->once())->method('getConfig')->willReturnSelf();
        $this->serializer->expects($this->once())->method('serialize')->with(json_decode($updatedConfig, true))
            ->willReturn($updatedConfig);
        $current->expects($this->once())->method('setConfig')->with($updatedConfig)->willReturnSelf();

        $this->model->execute();
    }

    /**
     * Tests that on bookmark switch the previous bookmark config gets updated with the current bookmark config
     * And that the selected bookmark is set as "current"
     *
     * @return void
     * @throws LocalizedException|\ReflectionException
     */
    public function testExecuteForUpdateCurrentBookmarkConfig() : void
    {
        $updatedConfig = '{"views":{"bookmark1":{"data":' . json_encode($this->getConfigData('P1', 2, 1)) . '}}}';
        $currentConfig = '{"current":' . json_encode($this->getConfigData('P1', 2, 1)) . '}';

        $this->userContext->expects($this->exactly(2))->method('getUserId')->willReturn(1);
        $bookmark = $this->getMockBuilder(BookmarkInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrent', 'getIdentifier'])
            ->getMockForAbstractClass();
        $this->bookmarkFactory->expects($this->once())->method('create')->willReturn($bookmark);

        $request = $this->getMockForAbstractClass(RequestInterface::class);
        $request->expects($this->atLeast(3))
            ->method('getParam')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['data'] => $currentConfig,
                ['namespace'] => 'product_listing'
            });
        $reflectionProperty = new \ReflectionProperty($this->model, '_request');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $request);

        $this->serializer->expects($this->once())->method('unserialize')->with($currentConfig)
            ->willReturn(json_decode($currentConfig, true));
        $current = $this->createBookmark();
        $bookmark1 = $this->createBookmark('bookmark1', '1', $this->getConfigData('P1', 1, 2));
        $bookmark2 = $this->createBookmark('bookmark2', '0', $this->getConfigData('P2', 1, 2));

        $this->bookmarkManagement->expects($this->once())->method('getByIdentifierNamespace')
            ->with(Save::CURRENT_IDENTIFIER, 'product_listing')
            ->willReturn($current);

        $current->expects($this->once())->method('setUserId')
            ->with(1)
            ->willReturnSelf();
        $current->expects($this->once())->method('setNamespace')
            ->with('product_listing')
            ->willReturnSelf();
        $current->expects($this->once())->method('setIdentifier')
            ->with(Save::CURRENT_IDENTIFIER)
            ->willReturnSelf();
        $current->expects($this->once())->method('setTitle')
            ->with(null)
            ->willReturnSelf();
        $current->expects($this->once())->method('setConfig')
            ->with($currentConfig)
            ->willReturnSelf();

        $this->bookmarkRepository->expects($this->exactly(2))->method('save')->with($current)->willReturnSelf();

        $searchResult = $this->createMock(BookmarkSearchResultsInterface::class);
        $searchResult->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([$current, $bookmark1, $bookmark2]);
        $this->bookmarkManagement->expects($this->once())->method('loadByNamespace')->willReturn($searchResult);
        $current->expects($this->once())->method('getCurrent')->willReturn(0);
        $bookmark1->expects($this->once())->method('getCurrent')->willReturn(1);
        $bookmark1->expects($this->once())->method('getConfig')->willReturnSelf();
        $bookmark1->expects($this->exactly(2))->method('getIdentifier')->willReturnSelf();
        $this->serializer->expects($this->once())->method('serialize')->with(json_decode($updatedConfig, true))
            ->willReturn($updatedConfig);
        $bookmark1->expects($this->once())->method('setConfig')->with($updatedConfig)->willReturnSelf();
        $this->model->execute();
    }

    /**
     * Creates a bookmark mock object
     *
     * @param string $identifier
     * @param string $current
     * @param array $config
     * @return BookmarkInterface
     */
    private function createBookmark(
        string $identifier = 'current',
        string $current = '0',
        array $config = []
    ): BookmarkInterface {
        if (empty($config)) {
            $config = [
                    'filters' => [
                        'applied' => [
                            'placeholder' => true
                        ]]
                    ,
                    'positions' => [
                        'entity_id' => 1,
                        'sku' => 2
                    ]
                ];
        }
        $bookmark = $this->getMockBuilder(BookmarkInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrent', 'getIdentifier'])
            ->getMockForAbstractClass();
        $bookmark->expects($this->any())->method('getCurrent')->willReturn($current);
        $bookmark->expects($this->any())->method('getIdentifier')->willReturn($identifier);
        $configData = [
            'views' => [
                $identifier => [
                    'data' => $config
                ]
            ]
        ];

        if ($identifier === 'current') {
            $configData = [
                $identifier => [
                    'data' => $config
                ]
            ];
        }

        $bookmark->expects($this->any())->method('getConfig')->willReturn($configData);
        return $bookmark;
    }

    /**
     * Prepare test data for filters and positions
     *
     * @param string $sku
     * @param int $entity_position
     * @param int $sku_position
     * @return array
     */
    private function getConfigData(string $sku, int $entity_position, int $sku_position): array
    {
        return [
            'filters' => [
                'applied' => [
                    'placeholder' => true,
                    'sku' => $sku
                ]
            ],
            'positions' => [
                'entity_id' => $entity_position,
                'sku' => $sku_position
            ]
        ];
    }
}
