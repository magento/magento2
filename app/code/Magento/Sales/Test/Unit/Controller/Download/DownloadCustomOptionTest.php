<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Download;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Model\Download\CustomOptionInfo;
use Magento\Sales\Controller\Download\DownloadCustomOption;
use Magento\Sales\Model\Download;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DownloadCustomOptionTest extends TestCase
{
    protected const ID = 1;

    protected const ORDER_ITEM_ID = 2;

    protected const OPTION_ID = 3;

    protected const SECRET_KEY = 'secret';

    /**
     * @var Forward|MockObject
     */
    protected $resultForwardMock;

    /**
     * @var Result|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $redirectMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var Download|MockObject
     */
    protected $downloadMock;

    /**
     * @var MockObject
     */
    protected $customOptionInfoMock;

    /**
     * @var DownloadCustomOption|MockObject
     */
    protected $objectMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();

        $this->redirectMock = $this->getMockBuilder(RedirectInterface::class)
            ->getMock();

        $this->resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();

        $this->downloadMock = $this->getMockBuilder(Download::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['downloadFile'])
            ->getMock();

        $this->customOptionInfoMock = $this->getMockBuilder(CustomOptionInfo::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['search'])
            ->getMock();

        $this->objectMock = $this->objectManager->getObject(
            DownloadCustomOption::class,
            [
                'request' => $this->requestMock,
                'redirect' =>  $this->redirectMock,
                'resultForwardFactory' => $this->resultForwardFactoryMock,
                'redirectFactory' => $this->resultRedirectFactoryMock,
                'messageManager' => $this->messageManagerMock,
                'download' => $this->downloadMock,
                'searcher' => $this->customOptionInfoMock
            ]
        );
    }

    /**
     * @param $orderItemId
     * @param $optionId
     * @param $id
     * @param $secret
     * @dataProvider getDataProvider
     */
    public function testExecute($orderItemId, $optionId, $id, $secret)
    {
        $resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->setMethods(['forward'])
            ->getMock();

        $this->resultForwardFactoryMock->expects($this->any())->method('create')->willReturn($resultForwardMock);

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setUrl'])
            ->getMock();

        $this->resultRedirectFactoryMock->expects($this->any())->method('create')->willReturn($resultRedirectMock);

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
            ['order_item_id', null, $orderItemId ],
            ['option_id',null, $optionId],
            ['id', null, $id],
            ['key', null, $secret]
        ]);

        $json='
        {
            "type": "image\/png",
            "title": "image.png",
            "quote_path": "custom_options\/quote\/A\/a\/AAAAA",
            "order_path": "custom_options\/order\/A\/a\/AAAAA",
            "fullpath": "\/pub\/media\/custom_options\/quote\/A\/a\/AAAAA",
            "size": "315404",
            "width": 400,
            "height": 532,
            "secret_key": "secret"
        }';

        if (!isset($secret)) {
            $this->customOptionInfoMock->expects($this->once())->method('search')->willReturn(json_decode($json, true));
            $this->redirectMock->expects($this->once())->method('getRefererUrl')->willReturn('referer_url');
            $resultRedirectMock->expects($this->once())->method('setUrl')->willReturnSelf();
        }

        if (isset($secret)) {
            $this->customOptionInfoMock->expects($this->once())->method('search')->willReturn(json_decode($json, true));
            $this->downloadMock->expects($this->once())->method('downloadFile');
        }

        if (!isset($id) && (!isset($orderItemId) || !($optionId))) {
            $this->customOptionInfoMock->expects($this->once())
                ->method('search')
                ->willThrowException(new NoSuchEntityException(__("Entity with id 1|2|3 not found")));
        }

        $this->objectMock->execute();
    }

    public function getDataProvider()
    {
        return [
            [
                'order_item_id' => self::ORDER_ITEM_ID,
                'option_id' => self::OPTION_ID,
                'id' => self::ID,
                'key' => 'secret'
            ],
            [
                'order_item_id' => null,
                'option_id' => self::OPTION_ID,
                'id' => self::ID,
                'key' => 'secret'
            ],
            [
                'order_item_id' => self::ORDER_ITEM_ID,
                'option_id' => null,
                'id' => self::ID,
                'key' => 'secret'
            ],
            [
                'order_item_id' => self::ORDER_ITEM_ID,
                'option_id' => self::OPTION_ID,
                'id' => null,
                'key' => 'secret'
            ],
            [
                'order_item_id' => self::ORDER_ITEM_ID,
                'option_id' => self::OPTION_ID,
                'id' => self::ID,
                'secret' => null
            ],
            [ //exceptions
                'order_item_id' => null,
                'option_id' => null,
                'id' => null,
                'secret' => null
            ]
        ];
    }
}
