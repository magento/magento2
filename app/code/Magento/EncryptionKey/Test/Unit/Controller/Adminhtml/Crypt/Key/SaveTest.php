<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\EncryptionKey\Test\Unit\Controller\Adminhtml\Crypt\Key;

/**
 * Test class for Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key\Save
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $encryptMock;

    /** @var \Magento\EncryptionKey\Model\ResourceModel\Key\Change|\PHPUnit\Framework\MockObject\MockObject */
    protected $changeMock;

    /** @var \Magento\Framework\App\CacheInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $cacheMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $managerMock;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $responseMock;

    /** @var \Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key\Save */
    protected $model;

    protected function setUp(): void
    {
        $this->encryptMock = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->changeMock = $this->getMockBuilder(\Magento\EncryptionKey\Model\ResourceModel\Key\Change::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->cacheMock = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPost'])
            ->getMockForAbstractClass();
        $this->managerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $helper->getObject(
            \Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key\Save::class,
            [
                'encryptor' => $this->encryptMock,
                'change' => $this->changeMock,
                'cache' => $this->cacheMock,
                'request' => $this->requestMock,
                'messageManager' => $this->managerMock,
                'response' => $this->responseMock,
            ]
        );
    }

    public function testExecuteNonRandomAndWithCryptKey()
    {
        $expectedMessage = 'The encryption key has been changed.';
        $key = 1;
        $newKey = 'RSASHA9000VERYSECURESUPERMANKEY';
        $this->requestMock
            ->expects($this->at(0))
            ->method('getPost')
            ->with($this->equalTo('generate_random'))
            ->willReturn(0);
        $this->requestMock
            ->expects($this->at(1))
            ->method('getPost')
            ->with($this->equalTo('crypt_key'))
            ->willReturn($key);
        $this->encryptMock->expects($this->once())->method('validateKey');
        $this->changeMock->expects($this->once())->method('changeEncryptionKey')->willReturn($newKey);
        $this->managerMock->expects($this->once())->method('addSuccessMessage')->with($expectedMessage);
        $this->cacheMock->expects($this->once())->method('clean');
        $this->responseMock->expects($this->once())->method('setRedirect');

        $this->model->execute();
    }

    public function testExecuteNonRandomAndWithoutCryptKey()
    {
        $key = null;
        $this->requestMock
            ->expects($this->at(0))
            ->method('getPost')
            ->with($this->equalTo('generate_random'))
            ->willReturn(0);
        $this->requestMock
            ->expects($this->at(1))
            ->method('getPost')
            ->with($this->equalTo('crypt_key'))
            ->willReturn($key);
        $this->managerMock->expects($this->once())->method('addErrorMessage');

        $this->model->execute();
    }

    public function testExecuteRandom()
    {
        $newKey = 'RSASHA9000VERYSECURESUPERMANKEY';
        $this->requestMock
            ->expects($this->at(0))
            ->method('getPost')
            ->with($this->equalTo('generate_random'))
            ->willReturn(1);
        $this->changeMock->expects($this->once())->method('changeEncryptionKey')->willReturn($newKey);
        $this->managerMock->expects($this->once())->method('addSuccessMessage');
        $this->managerMock->expects($this->once())->method('addNoticeMessage');
        $this->cacheMock->expects($this->once())->method('clean');
        $this->responseMock->expects($this->once())->method('setRedirect');

        $this->model->execute();
    }
}
