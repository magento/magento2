<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Test\Unit\Controller\Adminhtml\Crypt\Key;

use Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key\Save;
use Magento\EncryptionKey\Model\ResourceModel\Key\Change;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key\Save
 */
class SaveTest extends TestCase
{
    /**
     * @var EncryptorInterface|MockObject
     */
    protected $encryptMock;

    /**
     * @var Change|MockObject
     */
    protected $changeMock;

    /**
     * @var CacheInterface|MockObject
     */
    protected $cacheMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $managerMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var Save
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->encryptMock = $this->getMockBuilder(EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMockForAbstractClass();
        $this->changeMock = $this->getMockBuilder(Change::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['changeEncryptionKey'])
            ->getMock();
        $this->cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPost'])
            ->getMockForAbstractClass();
        $this->managerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setRedirect'])
            ->getMockForAbstractClass();

        $helper = new ObjectManager($this);

        $this->model = $helper->getObject(
            Save::class,
            [
                'encryptor' => $this->encryptMock,
                'change' => $this->changeMock,
                'cache' => $this->cacheMock,
                'request' => $this->requestMock,
                'messageManager' => $this->managerMock,
                'response' => $this->responseMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteNonRandomAndWithCryptKey(): void
    {
        $expectedMessage = 'The encryption key has been changed.';
        $key = 1;
        $newKey = 'RSASHA9000VERYSECURESUPERMANKEY';
        $this->requestMock
            ->method('getPost')
            ->withConsecutive(['generate_random'], ['crypt_key'])
            ->willReturnOnConsecutiveCalls(0, $key);
        $this->encryptMock->expects($this->once())->method('validateKey');
        $this->changeMock->expects($this->once())->method('changeEncryptionKey')->willReturn($newKey);
        $this->managerMock->expects($this->once())->method('addSuccessMessage')->with($expectedMessage);
        $this->cacheMock->expects($this->once())->method('clean');
        $this->responseMock->expects($this->once())->method('setRedirect');

        $this->model->execute();
    }

    /**
     * @return void
     */
    public function testExecuteNonRandomAndWithoutCryptKey(): void
    {
        $key = null;
        $this->requestMock
            ->method('getPost')
            ->withConsecutive(['generate_random'], ['crypt_key'])
            ->willReturnOnConsecutiveCalls(0, $key);
        $this->managerMock->expects($this->once())->method('addErrorMessage');

        $this->model->execute();
    }

    /**
     * @return void
     */
    public function testExecuteRandom(): void
    {
        $newKey = 'RSASHA9000VERYSECURESUPERMANKEY';
        $this->requestMock
            ->method('getPost')
            ->withConsecutive(['generate_random'])
            ->willReturnOnConsecutiveCalls(1);

        $this->changeMock->expects($this->once())->method('changeEncryptionKey')->willReturn($newKey);
        $this->managerMock->expects($this->once())->method('addSuccessMessage');
        $this->managerMock->expects($this->once())->method('addNoticeMessage');
        $this->cacheMock->expects($this->once())->method('clean');
        $this->responseMock->expects($this->once())->method('setRedirect');

        $this->model->execute();
    }
}
