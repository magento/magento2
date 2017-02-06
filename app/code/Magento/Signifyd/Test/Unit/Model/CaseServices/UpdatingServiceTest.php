<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
use Magento\Signifyd\Model\OrderGridUpdater;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Contains tests with different negative and positive scenarios for case updating service.
 */
class UpdatingServiceTest extends \PHPUnit_Framework_TestCase
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->messageGenerator = $this->getMockBuilder(GeneratorInterface::class)
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

        $this->orderGridUpdater = $this->getMockBuilder(OrderGridUpdater::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = $this->objectManager->getObject(UpdatingService::class, [
            'messageGenerator' => $this->messageGenerator,
            'caseRepository' => $this->caseRepository,
            'commentsHistoryUpdater' => $this->commentsHistoryUpdater,
            'orderGridUpdater' => $this->orderGridUpdater
        ]);
    }

    /**
     * Checks a test case when Signifyd case is empty entity.
     *
     * @covers \Magento\Signifyd\Model\CaseServices\UpdatingService::update
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The case entity should not be empty.
     */
    public function testUpdateWithEmptyCaseEntity()
    {
        $data = [];
        $caseEntity = $this->withCaseEntity(null, 123, $data);

        $this->service->update($caseEntity, $data);
    }

    /**
     * Checks a test case when Signifyd case id is not specified for a case entity.
     *
     * @covers \Magento\Signifyd\Model\CaseServices\UpdatingService::update
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The case entity should not be empty.
     */
    public function testUpdateWithEmptyCaseId()
    {
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot update Case entity.
     */
    public function testUpdateWithExceptionFromMessageGenerator()
    {
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot update Case entity.
     */
    public function testUpdateWithFailedCommentSaving()
    {
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

        $this->caseRepository->expects(self::once())
            ->method('save')
            ->with($caseEntity)
            ->willReturn($caseEntity);

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
            ->with($caseEntity, $message);

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
