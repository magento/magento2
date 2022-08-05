<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\ResourceModel\UserProfile as ResourceUserProfile;
use Magento\AdobeIms\Model\UserProfile;
use Magento\AdobeIms\Model\UserProfileRepository;
use Magento\AdobeImsApi\Api\Data\UserProfileInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * User repository test.
 */
class UserProfileRepositoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UserProfileRepository $model
     */
    private $model;

    /**
     * @var ResourceUserProfile|MockObject $resource
     */
    private $resource;

    /**
     * @var UserProfileInterfaceFactory|MockObject $entityFactory
     */
    private $entityFactory;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->resource = $this->createMock(ResourceUserProfile::class);
        $this->entityFactory =  $this->createMock(UserProfileInterfaceFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->model = new UserProfileRepository(
            $this->resource,
            $this->entityFactory,
            $this->loggerMock
        );
    }

    /**
     * Test save.
     */
    public function testSave(): void
    {
        $userProfile = $this->objectManager->getObject(UserProfile::class);
        $this->resource->expects($this->once())
            ->method('save')
            ->with($userProfile);
        $this->model->save($userProfile);
    }

    /**
     * Test save with exception.
     */
    public function testSaveWithException(): void
    {
        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save user profile.');

        $userProfile = $this->createMock(UserProfile::class);
        $this->resource->expects($this->once())
            ->method('save')
            ->with($userProfile)
            ->willThrowException(
                new CouldNotSaveException(__('Could not save user profile.'))
            );
        $this->loggerMock->expects($this->once())->method('critical');
        $this->model->save($userProfile);
    }

    /**
     * Test get  id.
     */
    public function testGet(): void
    {
        $entity = $this->objectManager->getObject(UserProfile::class)->setId(1);
        $this->entityFactory->method('create')
            ->willReturn($entity);
        $this->assertEquals($this->model->get(1)->getId(), 1);
    }

    /**
     * Test get user id with exception.
     */
    public function testGeWithException(): void
    {
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('The user profile wasn\'t found.');

        $entity = $this->objectManager->getObject(UserProfile::class);
        $this->entityFactory->method('create')
            ->willReturn($entity);
        $this->resource->expects($this->once())
            ->method('load')
            ->willThrowException(
                new NoSuchEntityException(__('The user profile wasn\'t found.'))
            );
        $this->model->get(1);
    }

    /**
     * Test get by user id.
     */
    public function testGetByUserId(): void
    {
        $entity = $this->objectManager->getObject(UserProfile::class)->setId(1);
        $this->entityFactory->method('create')
            ->willReturn($entity);
        $this->assertEquals($this->model->getByUserId(1)->getId(), 1);
    }
}
