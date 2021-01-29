<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\CaseServices;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CaseServices\UpdatingService;
use Magento\Signifyd\Model\CommentsHistoryUpdater;
use Magento\Signifyd\Model\MessageGenerators\GeneratorException;
use Magento\Signifyd\Model\MessageGenerators\GeneratorInterface;
use Magento\Signifyd\Model\OrderStateService;
use Magento\Signifyd\Model\SalesOrderGrid\OrderGridUpdater;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Contains tests with different negative and positive scenarios for case updating service.
 */
class UpdatingServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UpdatingService
     */
    private $service;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GeneratorInterface|MockObject
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
     * @var OrderGridUpdater|MockObject
     */
    private $orderGridUpdater;

    /**
     * @var OrderStateService|MockObject
     */
    private $orderStateService;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->messageGenerator = $this->getMockBuilder(GeneratorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMockForAbstractClass();

        $this->caseRepository = $this->getMockBuilder(CaseRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByCaseId'])
            ->getMockForAbstractClass();

        $this->commentsHistoryUpdater = $this->getMockBuilder(CommentsHistoryUpdater::class)
            ->disableOriginalConstructor()
            ->setMethods(['addComment'])
            ->getMock();

        $this->orderGridUpdater = $this->getMockBuilder(OrderGridUpdater::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderStateService = $this->getMockBuilder(OrderStateService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = $this->objectManager->getObject(UpdatingService::class, [
            'messageGenerator' => $this->messageGenerator,
            'caseRepository' => $this->caseRepository,
            'commentsHistoryUpdater' => $this->commentsHistoryUpdater,
            'orderGridUpdater' => $this->orderGridUpdater,
            'orderStateService' => $this->orderStateService
        ]);
    }

    /**
     * Checks a test case when Signifyd case is empty entity.
     *
     * @covers \Magento\Signifyd\Model\CaseServices\UpdatingService::update
     */
    public function testUpdateWithEmptyCaseEntity()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The case entity should not be empty.');

        $data = [];
        $caseEntity = $this->withCaseEntity(null, 123, $data);

        $this->service->update($caseEntity, $data);
    }

    /**
     * Checks a test case when Signifyd case id is not specified for a case entity.
     *
     * @covers \Magento\Signifyd\Model\CaseServices\UpdatingService::update
     */
    public function testUpdateWithEmptyCaseId()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The case entity should not be empty.');

        $data = [
            'caseId' => 123
        ];
        $caseEntity = $this->withCaseEntity(1, null, $data);

        $this->service->update($caseEntity, $data);
    }

    /**
     * Checks as test case when service cannot save Signifyd case entity
     *
     * @covers \Magento\Signifyd\Model\CaseServices\UpdatingService::update
     */
    public function testUpdateWithFailedCaseSaving()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Cannot update Case entity.');

        $caseId = 123;
        $data = [
            'caseId' => $caseId,
            'status' => CaseInterface::STATUS_OPEN,
            'orderId' => '10000012',
            'score' => 500
        ];

        $caseEntity = $this->withCaseEntity(1, $caseId, $data);

        $this->caseRepository->expects(self::once())
            ->method('save')
            ->willThrowException(new \Exception('Something wrong.'));

        $this->service->update($caseEntity, $data);
    }

    /**
     * Checks as test case when message generator throws an exception
     *
     * @covers \Magento\Signifyd\Model\CaseServices\UpdatingService::update
     */
    public function testUpdateWithExceptionFromMessageGenerator()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Cannot update Case entity.');

        $caseId = 123;
        $data = [
            'caseId' => $caseId
        ];

        $caseEntity = $this->withCaseEntity(1, $caseId, $data);

        $this->caseRepository->expects(self::never())
            ->method('save')
            ->with($caseEntity)
            ->willReturn($caseEntity);

        $this->messageGenerator->expects(self::once())
            ->method('generate')
            ->with($data)
            ->willThrowException(new GeneratorException(__('Cannot generate message.')));

        $this->service->update($caseEntity, $data);
    }

    /**
     * Checks a test case when comments history updater throws an exception.
     *
     * @covers \Magento\Signifyd\Model\CaseServices\UpdatingService::update
     */
    public function testUpdateWithFailedCommentSaving()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Cannot update Case entity.');

        $caseId = 123;
        $data = [
            'caseId' => $caseId,
            'orderId' => 1
        ];

        $caseEntity = $this->withCaseEntity(1, $caseId, $data);

        $this->caseRepository->expects(self::once())
            ->method('save')
            ->with($caseEntity)
            ->willReturn($caseEntity);

        $this->orderGridUpdater->expects(self::once())
            ->method('update')
            ->with($data['orderId']);

        $message = __('Message is generated.');
        $this->messageGenerator->expects(self::once())
            ->method('generate')
            ->with($data)
            ->willReturn($message);

        $this->commentsHistoryUpdater->expects(self::once())
            ->method('addComment')
            ->with($caseEntity, $message)
            ->willThrowException(new \Exception('Something wrong'));

        $this->service->update($caseEntity, $data);
    }

    /**
     * Checks a test case when Signifyd case entity is successfully updated and message stored in comments history.
     *
     * @covers \Magento\Signifyd\Model\CaseServices\UpdatingService::update
     */
    public function testUpdate()
    {
        $caseId = 123;
        $data = [
            'caseId' => $caseId,
            'orderId' => 1
        ];

        $caseEntity = $this->withCaseEntity(21, $caseId, $data);

        $caseEntitySaved = clone $caseEntity;
        $caseEntitySaved->expects(self::once())
            ->method('getGuaranteeDisposition')
            ->willReturn('APPROVED');

        $this->caseRepository->expects(self::once())
            ->method('save')
            ->with($caseEntity)
            ->willReturn($caseEntitySaved);

        $message = __('Message is generated.');
        $this->messageGenerator->expects(self::once())
            ->method('generate')
            ->with($data)
            ->willReturn($message);

        $this->orderGridUpdater->expects(self::once())
            ->method('update')
            ->with($data['orderId']);

        $this->commentsHistoryUpdater->expects(self::once())
            ->method('addComment')
            ->with($caseEntitySaved, $message);

        $this->orderStateService->expects(self::once())
            ->method('updateByCase')
            ->with($caseEntitySaved);

        $this->service->update($caseEntity, $data);
    }

    /**
     * Create mock for case entity with common scenarios.
     *
     * @param $caseEntityId
     * @param $caseId
     * @param array $data
     * @return CaseInterface|MockObject
     */
    private function withCaseEntity($caseEntityId, $caseId, array $data = [])
    {
        /** @var CaseInterface|MockObject $caseEntity */
        $caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getEntityId', 'getCaseId', 'getOrderId',
                'setCaseId', 'setStatus', 'setOrderId', 'setScore'
            ])
            ->getMockForAbstractClass();

        $caseEntity->expects(self::any())
            ->method('getEntityId')
            ->willReturn($caseEntityId);
        $caseEntity->expects(self::any())
            ->method('getCaseId')
            ->willReturn($caseId);

        foreach ($data as $property => $value) {
            $method = 'set' . ucfirst($property);
            if ($property === 'orderId') {
                $caseEntity->expects(self::never())
                    ->method($method);
            }
            $caseEntity->expects(self::any())
                ->method($method)
                ->with(self::equalTo($value))
                ->willReturnSelf();

            $method = 'get' . ucfirst($property);
            $caseEntity->expects(self::any())
                ->method($method)
                ->willReturn($value);
        }

        return $caseEntity;
    }
}
