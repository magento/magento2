<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CaseUpdatingService;
use Magento\Signifyd\Model\CommentsHistoryUpdater;
use Magento\Signifyd\Model\MessageGeneratorException;
use Magento\Signifyd\Model\MessageGeneratorInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Contains tests with different negative and positive scenarios for case updating service.
 */
class CaseUpdatingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CaseUpdatingService
     */
    private $service;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MessageGeneratorInterface|MockObject
     */
    private $messageGenerator;

    /**
     * @var CaseRepositoryInterface|MockObject
     */
    private $caseRepository;

    /**
     * @var CommentsHistoryUpdater|MockObject
     */
    private $commentsHistoryUpdater;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->messageGenerator = $this->getMockBuilder(MessageGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();

        $this->caseRepository = $this->getMockBuilder(CaseRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByCaseId'])
            ->getMockForAbstractClass();

        $this->commentsHistoryUpdater = $this->getMockBuilder(CommentsHistoryUpdater::class)
            ->disableOriginalConstructor()
            ->setMethods(['addComment'])
            ->getMock();

        $this->service = $this->objectManager->getObject(CaseUpdatingService::class, [
            'messageGenerator' => $this->messageGenerator,
            'caseRepository' => $this->caseRepository,
            'commentsHistoryUpdater' => $this->commentsHistoryUpdater
        ]);
    }

    /**
     * Checks a test case when Signifyd case id is missed in input data.
     *
     * @covers \Magento\Signifyd\Model\CaseUpdatingService::update
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The "caseId" should not be empty.
     */
    public function testUpdateWithFailedValidation()
    {
        $data = [];

        $this->service->update($data);
    }

    /**
     * Checks a test case when Signifyd case entity not found in repository.
     *
     * @covers \Magento\Signifyd\Model\CaseUpdatingService::update
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Case entity not found.
     */
    public function testUpdateWithNotExistingCase()
    {
        $caseId = 123;
        $data = [
            'caseId' => $caseId
        ];

        $this->caseRepository->expects(self::once())
            ->method('getByCaseId')
            ->with($caseId)
            ->willReturn(null);

        $this->service->update($data);
    }

    /**
     * Checks as test case when service cannot save Signifyd case entity
     *
     * @covers \Magento\Signifyd\Model\CaseUpdatingService::update
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot update Case entity.
     */
    public function testUpdateWithFailedCaseSaving()
    {
        $caseId = 123;
        $data = [
            'caseId' => $caseId,
            'status' => CaseInterface::STATUS_OPEN,
            'orderId' => '10000012',
            'score' => 500
        ];

        $caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCaseId', 'setStatus', 'setOrderId', 'setScore'])
            ->getMockForAbstractClass();

        $this->caseRepository->expects(self::once())
            ->method('getByCaseId')
            ->with($caseId)
            ->willReturn($caseEntity);

        $caseEntity->expects(self::never())
            ->method('setOrderId');
        $caseEntity->expects(self::once())
            ->method('setCaseId')
            ->with($caseId)
            ->willReturnSelf();
        $caseEntity->expects(self::once())
            ->method('setStatus')
            ->with(CaseInterface::STATUS_OPEN)
            ->willReturnSelf();
        $caseEntity->expects(self::once())
            ->method('setScore')
            ->with(500)
            ->willReturnSelf();

        $this->caseRepository->expects(self::once())
            ->method('save')
            ->willThrowException(new \Exception('Something wrong.'));

        $this->service->update($data);
    }

    /**
     * Checks as test case when message generator throws an exception
     *
     * @covers \Magento\Signifyd\Model\CaseUpdatingService::update
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot update Case entity.
     */
    public function testUpdateWithExceptionFromMessageGenerator()
    {
        $caseId = 123;
        $data = [
            'caseId' => $caseId
        ];

        $caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCaseId'])
            ->getMockForAbstractClass();

        $this->caseRepository->expects(self::once())
            ->method('getByCaseId')
            ->with($caseId)
            ->willReturn($caseEntity);

        $caseEntity->expects(self::once())
            ->method('setCaseId')
            ->with($caseId)
            ->willReturnSelf();

        $this->caseRepository->expects(self::never())
            ->method('save')
            ->with($caseEntity)
            ->willReturn($caseEntity);

        $this->messageGenerator->expects(self::once())
            ->method('generate')
            ->with($data)
            ->willThrowException(new MessageGeneratorException(__('Cannot generate message.')));

        $this->service->update($data);
    }

    /**
     * Checks a test case when comments history updater throws an exception.
     *
     * @covers \Magento\Signifyd\Model\CaseUpdatingService::update
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot update Case entity.
     */
    public function testUpdateWithFailedCommentSaving()
    {
        $caseId = 123;
        $data = [
            'caseId' => $caseId
        ];

        $caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCaseId'])
            ->getMockForAbstractClass();

        $this->caseRepository->expects(self::once())
            ->method('getByCaseId')
            ->with($caseId)
            ->willReturn($caseEntity);

        $caseEntity->expects(self::once())
            ->method('setCaseId')
            ->with($caseId)
            ->willReturnSelf();

        $this->caseRepository->expects(self::once())
            ->method('save')
            ->with($caseEntity)
            ->willReturn($caseEntity);

        $message = __('Message is generated.');
        $this->messageGenerator->expects(self::once())
            ->method('generate')
            ->with($data)
            ->willReturn($message);

        $this->commentsHistoryUpdater->expects(self::once())
            ->method('addComment')
            ->with($caseEntity, $message)
            ->willThrowException(new \Exception('Something wrong'));

        $this->service->update($data);
    }

    /**
     * Checks a test case when Signifyd case entity is successfully updated and message stored in comments history.
     *
     * @covers \Magento\Signifyd\Model\CaseUpdatingService::update
     */
    public function testUpdate()
    {
        $caseId = 123;
        $data = [
            'caseId' => $caseId
        ];

        $caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCaseId'])
            ->getMockForAbstractClass();

        $this->caseRepository->expects(self::once())
            ->method('getByCaseId')
            ->with($caseId)
            ->willReturn($caseEntity);

        $caseEntity->expects(self::once())
            ->method('setCaseId')
            ->with($caseId)
            ->willReturnSelf();

        $this->caseRepository->expects(self::once())
            ->method('save')
            ->with($caseEntity)
            ->willReturn($caseEntity);

        $message = __('Message is generated.');
        $this->messageGenerator->expects(self::once())
            ->method('generate')
            ->with($data)
            ->willReturn($message);

        $this->commentsHistoryUpdater->expects(self::once())
            ->method('addComment')
            ->with($caseEntity, $message);

        $this->service->update($data);
    }
}
