<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Controller\Adminhtml\User;

use Magento\AdobeIms\Controller\Adminhtml\User\Profile;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Ensure that User Profile data can be returned.
 */
class ProfileTest extends TestCase
{
    /**
     * @var MockObject|UserProfileRepositoryInterface
     */
    private $userProfileRepository;

    /**
     * @var MockObject|UserContextInterface
     */
    private $userContext;

    /**
     * @var MockObject|Context
     */
    private $action;

    /**
     * @var MockObject|ResultFactory
     */
    private $resultFactory;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var Profile
     */
    private $profile;

    /**
     * @var MockObject
     */
    private $jsonObject;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->action = $this->createMock(Context::class);

        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->userProfileRepository = $this->createMock(UserProfileRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->jsonObject = $this->createMock(Json::class);
        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->action->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);
        $this->resultFactory->expects($this->once())->method('create')->with('json')->willReturn($this->jsonObject);
        $this->profile = new Profile(
            $this->action,
            $this->userContext,
            $this->userProfileRepository,
            $this->logger
        );
    }

    /**
     * Ensure that User Profile data can be returned.
     *
     * @dataProvider userDataProvider
     * @param array $result
     * @throws NotFoundException
     */
    public function testExecute(array $result): void
    {
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $userProfileMock = $this->createMock(UserProfileInterface::class);
        $userProfileMock->expects($this->once())->method('getEmail')->willReturn('exaple@adobe.com');
        $userProfileMock->expects($this->once())->method('getName')->willReturn('Smith');
        $userProfileMock->expects($this->once())->method('getImage')->willReturn('https://adobe.com/sample-image.png');

        $this->userProfileRepository->expects($this->exactly(1))
            ->method('getByUserId')
            ->willReturn($userProfileMock);

        $this->jsonObject->expects($this->once())->method('setHttpResponseCode')->with(200);
        $this->jsonObject->expects($this->once())->method('setData')
            ->with($this->equalTo($result));
        $this->assertEquals($this->jsonObject, $this->profile->execute());
    }

    /**
     * Execute with exception
     */
    public function testExecuteWithExecption(): void
    {
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(null);
        $this->userProfileRepository->expects($this->exactly(1))
            ->method('getByUserId')
            ->willThrowException(new NoSuchEntityException());
        $result = [
            'success' => false,
            'message' => __('An error occurred during get user data. Contact support.'),
        ];
        $this->jsonObject->expects($this->once())->method('setHttpResponseCode')->with(500);
        $this->jsonObject->expects($this->once())->method('setData')
            ->with($this->equalTo($result));
        $this->profile->execute();
    }

    /**
     * User data provider
     *
     * @return array
     */
    public function userDataProvider(): array
    {
        return
            [
                [
                    [
                        'success' => true,
                        'error_message' => '',
                        'result' => [
                            'email' => 'exaple@adobe.com',
                            'name' => 'Smith',
                            'image' => 'https://adobe.com/sample-image.png'
                        ]
                    ]
                ]
            ];
    }
}
