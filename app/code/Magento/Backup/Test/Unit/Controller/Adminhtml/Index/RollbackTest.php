<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Test\Unit\Controller\Adminhtml\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class RollbackTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RollbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $context;

    /**
     * @var \Magento\Backup\Controller\Adminhtml\Index\Rollback
     */
    private $rollbackController;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Backup\Model\BackupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupModelFactoryMock;

    /**
     * @var \Magento\Backup\Model\Backup|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupModelMock;

    /**
     * @var \Magento\Backup\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataHelperMock;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRawFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRawMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultForwardMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->getMock();
        $this->requestMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            true,
            true,
            ['initForward', 'setDispatched', 'isAjax']
        );
        $this->responseMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\ResponseInterface',
            [],
            '',
            false,
            true,
            true,
            ['setRedirect']
        );
        $this->backupModelFactoryMock = $this->getMockBuilder('Magento\Backup\Model\BackupFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->backupModelMock = $this->getMockBuilder('Magento\Backup\Model\Backup')
            ->disableOriginalConstructor()
            ->setMethods(['getTime', 'exists', 'getSize', 'output'])
            ->getMock();
        $this->dataHelperMock = $this->getMockBuilder('Magento\Backup\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(['isRollbackAllowed'])
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder('Magento\Framework\App\Response\Http\FileFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->context = $this->objectManager->getObject(
            'Magento\Backend\App\Action\Context',
            [
                'objectManager' => $this->objectManagerMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );
        $this->rollbackController = $this->objectManager->getObject(
            'Magento\Backup\Controller\Adminhtml\Index\Rollback',
            [
                'context' => $this->context,
                'backupModelFactory' => $this->backupModelFactoryMock,
                'fileFactory' => $this->fileFactoryMock
            ]
        );
    }

    public function testExecuteRollbackDisabled()
    {
        $rollbackAllowed = false;

        $this->dataHelperMock->expects($this->once())
            ->method('isRollbackAllowed')
            ->willReturn($rollbackAllowed);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Backup\Helper\Data')
            ->willReturn($this->dataHelperMock);
        
        $this->rollbackController->execute();
    }
}
