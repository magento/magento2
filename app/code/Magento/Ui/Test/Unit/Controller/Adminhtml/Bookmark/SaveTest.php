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
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Ui\Controller\Adminhtml\Bookmark\Save;
use Magento\Backend\App\Action\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Save controller test.
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
}
