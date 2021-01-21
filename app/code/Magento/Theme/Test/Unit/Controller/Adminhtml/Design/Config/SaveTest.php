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
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Theme\Model\DesignConfigRepository|\PHPUnit\Framework\MockObject\MockObject */
    protected $designConfigRepository;

    /** @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $redirectFactory;

    /** @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject */
    protected $redirect;

    /** @var \Magento\Theme\Model\Data\Design\ConfigFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $configFactory;

    /** @var \Magento\Framework\App\ScopeValidatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $scopeValidator;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $messageManager;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $request;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject */
    protected $context;

    /** @var \Zend\Stdlib\Parameters|\PHPUnit\Framework\MockObject\MockObject */
    protected $fileParams;

    /** @var \Magento\Theme\Api\Data\DesignConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $designConfig;

    /** @var Save */
    protected $controller;

    /** @var \Magento\Framework\App\Request\DataPersistorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $dataPersistor;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->designConfigRepository = $this->createMock(\Magento\Theme\Model\DesignConfigRepository::class);
        $this->redirectFactory = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);
        $this->redirect = $this->createMock(\Magento\Backend\Model\View\Result\Redirect::class);
        $this->configFactory = $this->createMock(\Magento\Theme\Model\Data\Design\ConfigFactory::class);
        $this->messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
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
            \Magento\Backend\App\Action\Context::class,
            [
                'request' => $this->request,
                'messageManager' => $this->messageManager,
                'resultRedirectFactory' => $this->redirectFactory
            ]
        );
        $this->designConfig = $this->getMockForAbstractClass(
            \Magento\Theme\Api\Data\DesignConfigInterface::class,
            [],
            '',
            false
        );
        $this->fileParams = $this->createMock(\Zend\Stdlib\Parameters::class);
        $this->dataPersistor = $this->getMockBuilder(\Magento\Framework\App\Request\DataPersistorInterface::class)
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
