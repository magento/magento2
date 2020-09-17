<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\StoreSwitcher;

use InvalidArgumentException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterfaceFactory;
use Magento\Store\Model\StoreSwitcher\HashProcessor;
use Magento\Store\Model\StoreSwitcher\RedirectDataInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataInterfaceFactory;
use Magento\Store\Model\StoreSwitcher\RedirectDataPostprocessorInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataSerializerInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HashProcessorTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $request;
    /**
     * @var RedirectDataPostprocessorInterface|MockObject
     */
    private $postprocessor;
    /**
     * @var RedirectDataSerializerInterface|MockObject
     */
    private $dataSerializer;
    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;
    /**
     * @var RedirectDataValidator|MockObject
     */
    private $dataValidator;
    /**
     * @var StoreInterface|MockObject
     */
    private $store1;
    /**
     * @var StoreInterface|MockObject
     */
    private $store2;
    /**
     * @var HashProcessor
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = $this->createMock(RequestInterface::class);
        $this->postprocessor = $this->createMock(RedirectDataPostprocessorInterface::class);
        $this->dataSerializer = $this->createMock(RedirectDataSerializerInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $contextFactory = $this->createMock(ContextInterfaceFactory::class);
        $dataFactory = $this->createMock(RedirectDataInterfaceFactory::class);
        $this->dataValidator = $this->createMock(RedirectDataValidator::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->store1 = $this->createMock(StoreInterface::class);
        $this->store2 = $this->createMock(StoreInterface::class);
        $this->model = new HashProcessor(
            $this->request,
            $this->postprocessor,
            $this->dataSerializer,
            $this->messageManager,
            $contextFactory,
            $dataFactory,
            $this->dataValidator,
            $logger
        );

        $contextFactory->method('create')
            ->willReturn($this->createMock(ContextInterface::class));
        $dataFactory->method('create')
            ->willReturnCallback(
                function (array $data) {
                    return $this->createConfiguredMock(
                        RedirectDataInterface::class,
                        [
                            'getTimestamp' => $data['timestamp'],
                            'getData' => $data['data'],
                            'getSignature' => $data['signature'],
                        ]
                    );
                }
            );
    }

    public function testShouldProcessIfDataValidationPassed(): void
    {
        $redirectUrl = '/category-1/category-1.1.html';
        $this->request->method('getParam')
            ->willReturnMap(
                [
                    ['time_stamp', null, time() - 1],
                    ['data', null, '{"customer_id":1}'],
                    ['signature', null, 'randomstring'],
                ]
            );
        $this->dataValidator->method('validate')
            ->willReturn(true);
        $this->dataSerializer->method('unserialize')
            ->with('{"customer_id":1}')
            ->willReturnCallback(
                function ($arg) {
                    return json_decode($arg, true);
                }
            );
        $this->postprocessor->expects($this->once())
            ->method('process')
            ->with($this->isInstanceOf(ContextInterface::class), ['customer_id' => 1]);
        $this->assertEquals($redirectUrl, $this->model->switch($this->store1, $this->store2, $redirectUrl));
    }

    public function testShouldNotProcessIfDataValidationFailed(): void
    {
        $redirectUrl = '/category-1/category-1.1.html';
        $this->dataValidator->method('validate')
            ->willReturn(false);
        $this->postprocessor->expects($this->never())
            ->method('process');
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('The requested store cannot be found. Please check the request and try again.');

        $this->assertEquals($redirectUrl, $this->model->switch($this->store1, $this->store2, $redirectUrl));
    }

    public function testShouldNotProcessIfDataUnserializationFailed(): void
    {
        $redirectUrl = '/category-1/category-1.1.html';
        $this->dataValidator->method('validate')
            ->willReturn(true);
        $this->dataSerializer->method('unserialize')
            ->willThrowException(new InvalidArgumentException('Invalid token supplied'));
        $this->postprocessor->expects($this->never())
            ->method('process');
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('Something went wrong.');

        $this->assertEquals($redirectUrl, $this->model->switch($this->store1, $this->store2, $redirectUrl));
    }
}
