<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Backend\Model\Session\AdminConfig
 */
namespace Magento\Backend\Test\Unit\Model\Session;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Model\Session\AdminConfig;
use Magento\Backend\Model\Url;
use Magento\Backend\Model\UrlFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\ValidatorInterface;
use Magento\Framework\ValidatorFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdminConfigTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ValidatorFactory|MockObject
     */
    private $validatorFactory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UrlFactory|MockObject
     */
    private $backendUrlFactory;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createPartialMock(
            Http::class,
            ['getBasePath', 'isSecure', 'getHttpHost']
        );
        $this->requestMock->expects($this->atLeastOnce())->method('getBasePath')->willReturn('/');
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getHttpHost')
            ->willReturn('init.host');
        $this->objectManager =  new ObjectManager($this);
        $this->validatorFactory = $this->getMockBuilder(ValidatorFactory::class)
            ->addMethods(['setInstanceName'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $backendUrl = $this->createMock(Url::class);
        $backendUrl->expects($this->once())->method('getBaseUrl')->willReturn('/');
        $this->backendUrlFactory = $this->createPartialMock(UrlFactory::class, ['create']);
        $this->backendUrlFactory->expects($this->any())->method('create')->willReturn($backendUrl);

        $this->filesystemMock = $this->createMock(Filesystem::class);
        $dirMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($dirMock);
    }

    public function testSetCookiePathNonDefault()
    {
        $mockFrontNameResolver = $this->getMockBuilder(FrontNameResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockFrontNameResolver->expects($this->once())
            ->method('getFrontName')
            ->willReturn('backend');

        $validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(true);
        $this->validatorFactory->expects($this->any())
            ->method('setInstanceName')
            ->willReturnSelf();
        $this->validatorFactory->expects($this->any())
            ->method('create')
            ->willReturn($validatorMock);
        $adminConfig = $this->objectManager->getObject(
            AdminConfig::class,
            [
                'validatorFactory' => $this->validatorFactory,
                'request' => $this->requestMock,
                'frontNameResolver' => $mockFrontNameResolver,
                'backendUrlFactory' => $this->backendUrlFactory,
                'filesystem' => $this->filesystemMock,
            ]
        );

        $this->assertEquals('/backend', $adminConfig->getCookiePath());
    }

    /**
     * Test for setting session name and secure_cookie for admin
     * @dataProvider requestSecureDataProvider
     * @param $secureRequest
     */
    public function testSetSessionSettingsByConstructor($secureRequest)
    {
        $sessionName = 'admin';
        $this->requestMock->expects($this->exactly(2))->method('isSecure')->willReturn($secureRequest);

        $validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(true);
        $this->validatorFactory->expects($this->any())
            ->method('setInstanceName')
            ->willReturnSelf();
        $this->validatorFactory->expects($this->any())
            ->method('create')
            ->willReturn($validatorMock);

        $adminConfig = $this->objectManager->getObject(
            AdminConfig::class,
            [
                'validatorFactory' => $this->validatorFactory,
                'request' => $this->requestMock,
                'sessionName' => $sessionName,
                'backendUrlFactory' => $this->backendUrlFactory,
                'filesystem' => $this->filesystemMock,
            ]
        );
        $this->assertSame($sessionName, $adminConfig->getName());
        $this->assertSame($secureRequest, $adminConfig->getCookieSecure());
    }

    /**
     * @return array
     */
    public static function requestSecureDataProvider()
    {
        return [[true], [false]];
    }
}
