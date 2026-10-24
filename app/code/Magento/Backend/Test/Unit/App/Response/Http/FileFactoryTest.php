<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\App\Response\Http;

use Magento\Backend\App\Response\Http\FileFactory as HttpFileFactory;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\Url;
use Magento\Framework\App\Response\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Response\FileFactory;

class FileFactoryTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_authMock;

    /**
     * @var MockObject
     */
    protected $_backendUrl;

    /**
     * @var MockObject
     */
    protected $_sessionMock;

    /**
     * @var MockObject
     */
    protected $_responseMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $objects = [
            [
                FileFactory::class,
                $this->createMock(FileFactory::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $this->_responseMock = $this->createPartialMock(
            Http::class,
            ['setRedirect', '__wakeup']
        );
        $this->_responseMock->expects(
            $this->any()
        )->method(
            'setRedirect'
        )->willReturn(
            $this->_responseMock
        );

        $this->_sessionMock = $this->createPartialMockWithReflection(
            Session::class,
            ['setIsUrlNotice']
        );
        $this->_backendUrl = $this->createMock(Url::class);
        $this->_authMock = $this->createMock(Auth::class);
        $this->_model = $this->objectManager->getObject(
            HttpFileFactory::class,
            [
                'response' => $this->_responseMock,
                'auth' => $this->_authMock,
                'backendUrl' => $this->_backendUrl,
                'session' => $this->_sessionMock
            ]
        );
    }

    public function testCreate()
    {
        $authStorageMock = $this->createPartialMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['isFirstPageAfterLogin', 'processLogout', 'processLogin']
        );
        $this->_authMock->expects($this->once())->method('getAuthStorage')->willReturn($authStorageMock);
        $authStorageMock->expects($this->once())->method('isFirstPageAfterLogin')->willReturn(true);
        $this->_sessionMock->expects($this->once())->method('setIsUrlNotice');
        $this->_model->create('fileName', null);
    }
}
