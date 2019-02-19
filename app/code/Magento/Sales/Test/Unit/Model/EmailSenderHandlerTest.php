<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test of sales emails sending observer.
 */
class EmailSenderHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Sales\Model\EmailSenderHandler
     */
    protected $object;

    /**
     * Email sender model mock.
     *
     * @var \Magento\Sales\Model\Order\Email\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailSender;

    /**
     * Entity resource model mock.
     *
     * @var \Magento\Sales\Model\ResourceModel\EntityAbstract|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityResource;

    /**
     * Entity collection model mock.
     *
     * @var \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityCollection;

    /**
     * Global configuration storage mock.
     *
     * @var \Magento\Framework\App\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $globalConfig;

    /**
     * @var \Magento\Sales\Model\Order\Email\Container\IdentityInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $identityContainerMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->emailSender = $this->createPartialMock(\Magento\Sales\Model\Order\Email\Sender::class, ['send']);

        $this->entityResource = $this->getMockForAbstractClass(
            \Magento\Sales\Model\ResourceModel\EntityAbstract::class,
            [],
            '',
            false,
            false,
            true,
            ['save']
        );

        $this->entityCollection = $this->getMockForAbstractClass(
            \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection::class,
            [],
            '',
            false,
            false,
            true,
            ['addFieldToFilter', 'getItems', 'addAttributeToSelect', 'getSelect']
        );

        $this->globalConfig = $this->createMock(\Magento\Framework\App\Config::class);

        $this->identityContainerMock = $this->createMock(
            \Magento\Sales\Model\Order\Email\Container\IdentityInterface::class
        );

        $this->storeManagerMock = $this->createMock(
            \Magento\Store\Model\StoreManagerInterface::class
        );

        $this->object = $objectManager->getObject(
            \Magento\Sales\Model\EmailSenderHandler::class,
            [
                'emailSender'       => $this->emailSender,
                'entityResource'    => $this->entityResource,
                'entityCollection'  => $this->entityCollection,
                'globalConfig'      => $this->globalConfig,
                'identityContainer' => $this->identityContainerMock,
                'storeManager'      => $this->storeManagerMock,
            ]
        );
    }

    /**
     * @param int $configValue
     * @param array|null $collectionItems
     * @param bool|null $emailSendingResult
     * @dataProvider executeDataProvider
     * @return void
     */
    public function testExecute($configValue, $collectionItems, $emailSendingResult)
    {
        $path = 'sales_email/general/async_sending';

        $this->globalConfig
            ->expects($this->at(0))
            ->method('getValue')
            ->with($path)
            ->willReturn($configValue);

        if ($configValue) {
            $this->entityCollection
                ->expects($this->at(0))
                ->method('addFieldToFilter')
                ->with('send_email', ['eq' => 1]);

            $this->entityCollection
                ->expects($this->at(1))
                ->method('addFieldToFilter')
                ->with('email_sent', ['null' => true]);

            $this->entityCollection
                ->expects($this->any())
                ->method('addAttributeToSelect')
                ->with('store_id')
                ->willReturnSelf();

            $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);

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

            if ($collectionItems) {

                /** @var \Magento\Sales\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject $collectionItem */
                $collectionItem = $collectionItems[0];

                $this->emailSender
                    ->expects($this->once())
                    ->method('send')
                    ->with($collectionItem, true)
                    ->willReturn($emailSendingResult);

                $storeMock = $this->createMock(\Magento\Store\Model\Store::class);

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

                if ($emailSendingResult) {
                    $collectionItem
                        ->expects($this->once())
                        ->method('setEmailSent')
                        ->with(true)
                        ->willReturn($collectionItem);

                    $this->entityResource
                        ->expects($this->once())
                        ->method('save')
                        ->with($collectionItem);
                }
            }
        }

        $this->object->sendEmails();
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        $entityModel = $this->getMockForAbstractClass(
            \Magento\Sales\Model\AbstractModel::class,
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
            ],
            [
                'configValue' => 1,
                'collectionItems' => [clone $entityModel],
                'emailSendingResult' => false,
            ],
            [
                'configValue' => 1,
                'collectionItems' => [],
                'emailSendingResult' => null,
            ],
            [
                'configValue' => 0,
                'collectionItems' => null,
                'emailSendingResult' => null,
            ]
        ];
    }
}
