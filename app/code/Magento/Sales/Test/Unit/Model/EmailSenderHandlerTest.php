<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\EmailSenderHandler;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Sales\Model\ResourceModel\EntityAbstract;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test of sales emails sending observer.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailSenderHandlerTest extends TestCase
{
    /**
     * Subject of testing.
     *
     * @var EmailSenderHandler
     */
    protected $object;

    /**
     * Email sender model mock.
     *
     * @var Sender|MockObject
     */
    protected $emailSender;

    /**
     * Entity resource model mock.
     *
     * @var EntityAbstract|MockObject
     */
    protected $entityResource;

    /**
     * Entity collection model mock.
     *
     * @var AbstractCollection|MockObject
     */
    protected $entityCollection;

    /**
     * Global configuration storage mock.
     *
     * @var Config|MockObject
     */
    protected $globalConfig;

    /**
     * @var IdentityInterface|MockObject
     */
    private $identityContainerMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var ValueFactory|MockObject
     */
    private $configValueFactory;

    /**
     * @var string
     */
    private $modifyStartFromDate = '-1 day';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->emailSender = $this->getMockBuilder(Sender::class)
            ->addMethods(['send'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->entityResource = $this->getMockForAbstractClass(
            EntityAbstract::class,
            [],
            '',
            false,
            false,
            true,
            ['saveAttribute']
        );

        $this->entityCollection = $this->getMockForAbstractClass(
            AbstractCollection::class,
            [],
            '',
            false,
            false,
            true,
            ['addFieldToFilter', 'getItems', 'addAttributeToSelect', 'getSelect']
        );

        $this->globalConfig = $this->createMock(Config::class);

        $this->identityContainerMock = $this->createMock(
            IdentityInterface::class
        );

        $this->storeManagerMock = $this->createMock(
            StoreManagerInterface::class
        );

        $this->configValueFactory = $this->createMock(
            ValueFactory::class
        );

        $this->object = $objectManager->getObject(
            EmailSenderHandler::class,
            [
                'emailSender'         => $this->emailSender,
                'entityResource'      => $this->entityResource,
                'entityCollection'    => $this->entityCollection,
                'globalConfig'        => $this->globalConfig,
                'identityContainer'   => $this->identityContainerMock,
                'storeManager'        => $this->storeManagerMock,
                'configValueFactory'  => $this->configValueFactory,
                'modifyStartFromDate' => $this->modifyStartFromDate
            ]
        );
    }

    /**
     * @param int $configValue
     * @param array|null $collectionItems
     * @param bool|null $emailSendingResult
     * @param int|null $expectedIsEmailSent
     *
     * @return void
     * @dataProvider executeDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute(
        int $configValue,
        ?array $collectionItems,
        ?bool $emailSendingResult,
        ?int $expectedIsEmailSent
    ): void {
        if ($collectionItems!=null && !empty($collectionItems)) {
            $collectionItems[0] = $collectionItems[0]($this);
        }
        $this->globalConfig
            ->method('getValue')
            ->willReturnCallback(function ($path) use ($configValue) {
                if ($path === 'sales_email/general/async_sending') {
                    return $configValue;
                }
                if ($path === 'sales_email/general/async_sending_attempts') {
                    return 3;
                }
                return null;
            });

        if ($configValue) {
            $nowDate = date('Y-m-d H:i:s');
            $fromDate = date('Y-m-d H:i:s', strtotime($nowDate . ' ' . $this->modifyStartFromDate));
            $this->entityCollection
                ->method('addFieldToFilter')
                ->willReturnCallback(
                    function ($arg1, $arg2) use ($fromDate) {
                        if ($arg1 == 'send_email' && $arg2 == ['eq' => 1]) {
                            return null;
                        } elseif ($arg1 == 'email_sent' &&
                            ($arg2 == ['null' => true] || $arg2 == ['lteq' => -1])) {
                            return null;
                        } elseif ($arg1 == 'created_at' && $arg2 == ['from' => $fromDate]) {
                            return null;
                        }
                    }
                );

            $this->entityCollection
                ->expects($this->any())
                ->method('addAttributeToSelect')
                ->with('store_id')
                ->willReturnSelf();

            $selectMock = $this->createMock(Select::class);

            $selectMock
                ->expects($this->atLeastOnce())
                ->method('group')
                ->with('store_id')
                ->willReturnSelf();

            $this->entityCollection
                ->expects($this->any())
                ->method('getSelect')
                ->willReturn($selectMock);

            $this->entityCollection
                ->expects($this->any())
                ->method('getItems')
                ->willReturn($collectionItems);

            /** @var Value|Encrypted|MockObject $valueMock */
            $backendModelMock = $this->getMockBuilder(Value::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['load', 'getId'])
                ->addMethods(['getUpdatedAt'])
                ->getMock();
            $backendModelMock->expects($this->once())->method('load')->willReturnSelf();
            $backendModelMock->expects($this->once())->method('getId')->willReturn(1);
            $backendModelMock->expects($this->once())->method('getUpdatedAt')->willReturn($nowDate);

            $this->configValueFactory->expects($this->once())
                ->method('create')
                ->willReturn($backendModelMock);

            if ($collectionItems) {

                /** @var AbstractModel|MockObject $collectionItem */
                $collectionItem = $collectionItems[0];

                $this->emailSender
                    ->expects($this->once())
                    ->method('send')
                    ->with($collectionItem, true)
                    ->willReturn($emailSendingResult);

                $storeMock = $this->createMock(Store::class);

                $this->storeManagerMock
                    ->expects($this->any())
                    ->method('getStore')
                    ->willReturn($storeMock);

                $this->identityContainerMock
                    ->expects($this->any())
                    ->method('setStore')
                    ->with($storeMock);

                $this->identityContainerMock
                    ->expects($this->any())
                    ->method('isEnabled')
                    ->willReturn(true);

                $collectionItem
                    ->expects($this->once())
                    ->method('setEmailSent')
                    ->with($expectedIsEmailSent)
                    ->willReturn($collectionItem);

                $this->entityResource
                    ->expects($this->once())
                    ->method('saveAttribute')
                    ->with($collectionItem);
            }
        }

        $this->object->sendEmails();
    }

    /**
     * @return array
     */
    public static function executeDataProvider(): array
    {
        $entityModel = static fn (self $testCase) => $testCase->getMockForAbstractClass(
            AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['setEmailSent', 'getOrder']
        );

        return [
            [
                'configValue' => 1,
                'collectionItems' => [clone $entityModel],
                'emailSendingResult' => true,
                'expectedIsEmailSent' => 1
            ],
            [
                'configValue' => 1,
                'collectionItems' => [clone $entityModel],
                'emailSendingResult' => false,
                'expectedIsEmailSent' => -2
            ],
            [
                'configValue' => 1,
                'collectionItems' => [],
                'emailSendingResult' => null,
                'expectedIsEmailSent' => 1
            ],
            [
                'configValue' => 0,
                'collectionItems' => null,
                'emailSendingResult' => null,
                'expectedIsEmailSent' => 1
            ]
        ];
    }
}
