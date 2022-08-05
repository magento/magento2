<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Bookmark;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Ui\Controller\Adminhtml\Bookmark\Save;
use Magento\Backend\App\Action\Context;
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

        $this->model = new Save(
            $this->context,
            $this->factory,
            $this->bookmarkRepository,
            $this->bookmarkManagement,
            $this->bookmarkFactory,
            $this->userContext,
            $this->jsonDecoder
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
     * Tests that on bookmark switch the previous bookmark config gets updated with the current bookmark config
     * And that the selected bookmark is set as "current"
     *
     * @return void
     */
    public function testExecuteForCurrentBookmarkUpdate() : void
    {
        $updatedConfig = '{"views":{"bookmark1":{"data":{"data":["config"]}}}}';
        $selectedIdentifier = 'bookmark2';

        $this->userContext->method('getUserId')->willReturn(1);
        $bookmark = $this->getMockForAbstractClass(BookmarkInterface::class);
        $this->bookmarkFactory->expects($this->once())->method('create')->willReturn($bookmark);

        $request = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class);
        $request->expects($this->atLeast(2))
            ->method('getParam')
            ->withConsecutive(['data'], ['namespace'])
            ->willReturnOnConsecutiveCalls(
                '{"' . Save::ACTIVE_IDENTIFIER. '":"' . $selectedIdentifier . '"}',
                'product_listing'
            );

        $reflectionProperty = new \ReflectionProperty($this->model, '_request');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $request);

        $current = $this->createBookmark();
        $bookmark1 = $this->createBookmark('bookmark1', '1', 'bookmark1_config');
        $bookmark2 = $this->createBookmark($selectedIdentifier, '0', $selectedIdentifier .'_config');

        $searchResult = $this->createMock(\Magento\Ui\Api\Data\BookmarkSearchResultsInterface::class);
        $searchResult->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([$current, $bookmark1, $bookmark2]);
        $this->bookmarkManagement->expects($this->once())->method('loadByNamespace')->willReturn($searchResult);
        $bookmark1->expects($this->once())->method('setConfig')->with($updatedConfig);
        $bookmark1->expects($this->once())->method('setCurrent')->with(false);
        $bookmark2->expects($this->once())->method('setCurrent')->with(true);
        $this->model->execute();
    }

    /**
     * Creates a bookmark mock object
     *
     * @param string $identifier
     * @param string $current
     * @param string $config
     * @return BookmarkInterface|MockObject
     */
    private function createBookmark(string $identifier = 'current', string $current = '0', string $config = 'config')
    {
        $bookmark = $this->getMockBuilder(BookmarkInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrent', 'getIdentifier'])
            ->getMockForAbstractClass();
        $bookmark->expects($this->any())->method('getCurrent')->willReturn($current);
        $bookmark->expects($this->any())->method('getIdentifier')->willReturn($identifier);
        $configData = [
            'views' => [
                $identifier => [
                    'data' => [
                        $config
                    ]
                ]
            ]
        ];

        if ($identifier === 'current') {
            $configData = [
                $identifier => [
                    'data' => [
                        $config
                    ]
                ]
            ];
        }

        $bookmark->expects($this->any())->method('getConfig')->willReturn($configData);
        return $bookmark;
    }
}
