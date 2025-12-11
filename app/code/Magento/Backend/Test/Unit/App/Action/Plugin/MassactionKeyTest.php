<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\App\Action\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Plugin\MassactionKey;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MassactionKeyTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var MassactionKey
     */
    protected $plugin;

    /**
     * @var MockObject|RequestInterface
     */
    protected $requestMock;

    /**
     * @var MockObject|AbstractAction
     */
    protected $subjectMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        
        $this->subjectMock = $this->createMock(AbstractAction::class);
        $this->requestMock = $this->createPartialMockWithReflection(
            Http::class,
            ['getPost', 'setPostValue']
        );

        $this->plugin = $objectManager->getObject(
            MassactionKey::class,
            [
                'subject' => $this->subjectMock,
                'request' => $this->requestMock
            ]
        );
    }

    /**
     * @param array|string $postData
     * @param array $convertedData
     *
     * @return void
     */
    #[DataProvider('beforeDispatchDataProvider')]
    public function testBeforeDispatchWhenMassactionPrepareKeyRequestExists(
        $postData,
        array $convertedData
    ): void {
        $this->requestMock
            ->method('getPost')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['massaction_prepare_key'] => 'key',
                ['key'] => $postData
            });
        $this->requestMock->expects($this->once())
            ->method('setPostValue')
            ->with('key', $convertedData);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    /**
     * @return array
     */
    public static function beforeDispatchDataProvider(): array
    {
        return [
            'post_data_is_array' => [['key'], ['key']],
            'post_data_is_string' => ['key, key_two', ['key', ' key_two']]
        ];
    }

    /**
     * @return void
     */
    public function testBeforeDispatchWhenMassactionPrepareKeyRequestNotExists(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('massaction_prepare_key')
            ->willReturn(false);
        $this->requestMock->expects($this->never())
            ->method('setPostValue');

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
