<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Controller\Url\Rewrite;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\Component\MassAction\Filter;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite\MassDelete;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection as Collection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory as CollectionFactory;
use Magento\UrlRewrite\Model\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Url rewrite mass delete action unit test class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassDeleteTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Filter|MockObject
     */
    private $filterMock;

    /**
     * @var Collection|MockObject
     */
    private $urlRewriteCollectionMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var MassDelete|object
     */
    private $massDeleteController;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * SetUp method
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->resultRedirectFactoryMock = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->resultRedirectMock = $this->createMock(Redirect::class);

        $this->contextMock = $this->createMock(Context::class);

        $this->filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->urlRewriteCollectionMock =
            $this->createMock(Collection::class);

        $this->massDeleteController = $this->objectManager->getObject(
            MassDelete::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock
            ]
        );
    }

    /**
     * Test mass delete action
     *
     * @throws LocalizedException
     */
    public function testMassDeleteAction(): void
    {
        $collection = [
            $this->getUrlRewriteMock(),
            $this->getUrlRewriteMock(),
            $this->getUrlRewriteMock(true)
        ];

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->urlRewriteCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->urlRewriteCollectionMock)
            ->willReturn($this->urlRewriteCollectionMock);

        $this->urlRewriteCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collection));

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) have been deleted.', 2));
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('A total of %1 record(s) haven\'t been deleted.', 1));

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massDeleteController->execute());
    }

    /**
     * Create url rewrite model mock
     *
     * @param bool $exception
     * @return MockObject
     */
    private function getUrlRewriteMock($exception = false): MockObject
    {
        $urlRewrite = $this->createPartialMock(UrlRewrite::class, ['delete']);
        $urlRewriteInvocationMocker = $urlRewrite->expects($this->once())
            ->method('delete');
        if ($exception) {
            $urlRewriteInvocationMocker->willThrowException(
                new \Exception('Test delete exception')
            );
        } else {
            $urlRewriteInvocationMocker->willReturn(true);
        }

        return $urlRewrite;
    }
}
