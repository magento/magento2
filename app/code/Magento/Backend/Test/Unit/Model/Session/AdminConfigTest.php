<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Backend\Model\Session\AdminConfig
 */
namespace Magento\Backend\Test\Unit\Model\Session;

class AdminConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\ValidatorFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorFactory;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Backend\Model\UrlFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $backendUrlFactory;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    protected function setUp()
    {
        $this->requestMock = $this->createPartialMock(
            \Magento\Framework\App\Request\Http::class,
            ['getBasePath', 'isSecure', 'getHttpHost']
        );
        $this->requestMock->expects($this->atLeastOnce())->method('getBasePath')->will($this->returnValue('/'));
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getHttpHost')
            ->will($this->returnValue('init.host'));
        $this->objectManager =  new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->validatorFactory = $this->getMockBuilder(\Magento\Framework\ValidatorFactory::class)
            ->setMethods(['setInstanceName', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        $backendUrl = $this->createMock(\Magento\Backend\Model\Url::class);
        $backendUrl->expects($this->once())->method('getBaseUrl')->will($this->returnValue('/'));
        $this->backendUrlFactory = $this->createPartialMock(\Magento\Backend\Model\UrlFactory::class, ['create']);
        $this->backendUrlFactory->expects($this->any())->method('create')->willReturn($backendUrl);

        $this->filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $dirMock = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($dirMock));
    }

    public function testSetCookiePathNonDefault()
    {
        $mockFrontNameResolver = $this->getMockBuilder(\Magento\Backend\App\Area\FrontNameResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockFrontNameResolver->expects($this->once())
            ->method('getFrontName')
            ->will($this->returnValue('backend'));

        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            \Magento\Backend\Model\Session\AdminConfig::class,
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

        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            \Magento\Backend\Model\Session\AdminConfig::class,
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

    public function requestSecureDataProvider()
    {
        return [[true], [false]];
    }
}
