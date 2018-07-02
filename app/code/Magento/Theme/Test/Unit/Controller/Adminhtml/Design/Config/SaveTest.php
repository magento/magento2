<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\Design\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Controller\Adminhtml\Design\Config\Save;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Theme\Model\DesignConfigRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $designConfigRepository;

    /** @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectFactory;

    /** @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirect;

    /** @var \Magento\Theme\Model\Data\Design\ConfigFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $configFactory;

    /** @var \Magento\Framework\App\ScopeValidatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeValidator;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManager;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Zend\Stdlib\Parameters|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileParams;

    /** @var \Magento\Theme\Api\Data\DesignConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $designConfig;

    /** @var Save */
    protected $controller;

    /** @var \Magento\Framework\App\Request\DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataPersistor;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->designConfigRepository = $this->getMock('Magento\Theme\Model\DesignConfigRepository', [], [], '', false);
        $this->redirectFactory = $this->getMock(
            'Magento\Backend\Model\View\Result\RedirectFactory',
            [],
            [],
            '',
            false,
            false,
            true,
            ['create']
        );
        $this->redirect = $this->getMock('Magento\Backend\Model\View\Result\Redirect', [], [], '', false);
        $this->configFactory = $this->getMock('Magento\Theme\Model\Data\Design\ConfigFactory', [], [], '', false);
        $this->messageManager = $this->getMockForAbstractClass(
            'Magento\Framework\Message\ManagerInterface',
            [],
            '',
            false
        );
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();

        $this->request->expects($this->atLeastOnce())
            ->method('isPost')
            ->willReturn(true);

        $this->context = $objectManager->getObject(
            'Magento\Backend\App\Action\Context',
            [
                'request' => $this->request,
                'messageManager' => $this->messageManager,
                'resultRedirectFactory' => $this->redirectFactory
            ]
        );
        $this->designConfig = $this->getMockForAbstractClass(
            'Magento\Theme\Api\Data\DesignConfigInterface',
            [],
            '',
            false
        );
        $this->fileParams = $this->getMock('Zend\Stdlib\Parameters', [], [], '', false);
        $this->dataPersistor = $this->getMockBuilder('Magento\Framework\App\Request\DataPersistorInterface')
            ->getMockForAbstractClass();
        $this->controller = new Save(
            $this->context,
            $this->designConfigRepository,
            $this->configFactory,
            $this->dataPersistor
        );
    }

    public function testSave()
    {
        $scope = 'sadfa';
        $scopeId = 0;
        $this->redirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->redirect);
        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(
                ['scope'],
                ['scope_id'],
                ['back', false]
            )
            ->willReturnOnConsecutiveCalls(
                $scope,
                $scopeId,
                true
            );
        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn(['header_default_title' => 'Default title']);

        $this->request->expects($this->once())
            ->method('getFiles')
            ->willReturn($this->fileParams);
        $this->fileParams->expects($this->once())
            ->method('toArray')
            ->willReturn([
                'header_logo' => [
                    'tmp_name' => '',
                    'error' => 4
                ]
            ]);
        $this->configFactory->expects($this->once())
            ->method('create')
            ->with($scope, $scopeId, ['header_default_title' => 'Default title'])
            ->willReturn($this->designConfig);
        $this->designConfigRepository->expects($this->once())
            ->method('save')
            ->with($this->designConfig);
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You saved the configuration.'));
        $this->dataPersistor->expects($this->once())
            ->method('clear')
            ->with('theme_design_config');
        $this->redirect->expects($this->exactly(2))
            ->method('setPath')
            ->withConsecutive(
                ['theme/design_config/'],
                ['theme/design_config/edit', ['scope' => $scope, 'scope_id' => $scopeId]]
            );

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testSaveWithLocalizedException()
    {
        $scope = 'sadfa';
        $scopeId = 0;
        $this->redirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->redirect);
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(
                ['scope'],
                ['scope_id']
            )
            ->willReturnOnConsecutiveCalls(
                $scope,
                $scopeId
            );
        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn(['header_default_title' => 'Default title']);

        $this->request->expects($this->once())
            ->method('getFiles')
            ->willReturn($this->fileParams);
        $this->fileParams->expects($this->once())
            ->method('toArray')
            ->willReturn([
                'header_logo' => [
                    'tmp_name' => '',
                    'error' => 4
                ]
            ]);
        $this->configFactory->expects($this->once())
            ->method('create')
            ->with($scope, $scopeId, ['header_default_title' => 'Default title'])
            ->willReturn($this->designConfig);
        $this->designConfigRepository->expects($this->once())
            ->method('save')
            ->with($this->designConfig)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('Exception message')));
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Exception message')->render());

        $this->dataPersistor->expects($this->once())
            ->method('set')
            ->with('theme_design_config', ['header_default_title' => 'Default title']);
        $this->redirect->expects($this->once())
            ->method('setPath')
            ->with('theme/design_config/edit', ['scope' => $scope, 'scope_id' => $scopeId]);

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testSaveWithException()
    {
        $scope = 'sadfa';
        $scopeId = 0;
        $this->redirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->redirect);
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(
                ['scope'],
                ['scope_id']
            )
            ->willReturnOnConsecutiveCalls(
                $scope,
                $scopeId
            );
        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn(['header_default_title' => 'Default title']);

        $this->request->expects($this->once())
            ->method('getFiles')
            ->willReturn($this->fileParams);
        $this->fileParams->expects($this->once())
            ->method('toArray')
            ->willReturn([
                'header_logo' => [
                    'tmp_name' => '',
                    'error' => 4
                ]
            ]);
        $this->configFactory->expects($this->once())
            ->method('create')
            ->with($scope, $scopeId, ['header_default_title' => 'Default title'])
            ->willReturn($this->designConfig);
        $exception = new \Exception(__('Exception message'));
        $this->designConfigRepository->expects($this->once())
            ->method('save')
            ->with($this->designConfig)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, 'Something went wrong while saving this configuration: Exception message');

        $this->dataPersistor->expects($this->once())
            ->method('set')
            ->with('theme_design_config', ['header_default_title' => 'Default title']);
        $this->redirect->expects($this->once())
            ->method('setPath')
            ->with('theme/design_config/edit', ['scope' => $scope, 'scope_id' => $scopeId]);

        $this->assertSame($this->redirect, $this->controller->execute());
    }
}
