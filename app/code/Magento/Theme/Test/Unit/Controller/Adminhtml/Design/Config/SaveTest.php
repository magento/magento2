<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\Design\Config;

use Laminas\Stdlib\Parameters;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ScopeValidatorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Theme\Controller\Adminhtml\Design\Config\Save;
use Magento\Theme\Model\Data\Design\ConfigFactory;
use Magento\Theme\Model\DesignConfigRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /** @var DesignConfigRepository|MockObject */
    protected $designConfigRepository;

    /** @var RedirectFactory|MockObject */
    protected $redirectFactory;

    /** @var Redirect|MockObject */
    protected $redirect;

    /** @var ConfigFactory|MockObject */
    protected $configFactory;

    /** @var ScopeValidatorInterface|MockObject */
    protected $scopeValidator;

    /** @var ManagerInterface|MockObject */
    protected $messageManager;

    /** @var RequestInterface|MockObject */
    protected $request;

    /** @var Context|MockObject */
    protected $context;

    /** @var Parameters|MockObject */
    protected $fileParams;

    /** @var DesignConfigInterface|MockObject */
    protected $designConfig;

    /** @var Save */
    protected $controller;

    /** @var DataPersistorInterface|MockObject */
    protected $dataPersistor;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->designConfigRepository = $this->createMock(DesignConfigRepository::class);
        $this->redirectFactory = $this->createMock(RedirectFactory::class);
        $this->redirect = $this->createMock(Redirect::class);
        $this->configFactory = $this->createMock(ConfigFactory::class);
        $this->messageManager = $this->getMockForAbstractClass(
            ManagerInterface::class,
            [],
            '',
            false
        );
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->expects($this->atLeastOnce())
            ->method('isPost')
            ->willReturn(true);

        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->request,
                'messageManager' => $this->messageManager,
                'resultRedirectFactory' => $this->redirectFactory
            ]
        );
        $this->designConfig = $this->getMockForAbstractClass(
            DesignConfigInterface::class,
            [],
            '',
            false
        );
        $this->fileParams = $this->createMock(Parameters::class);
        $this->dataPersistor = $this->getMockBuilder(DataPersistorInterface::class)
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
            ->willThrowException(new LocalizedException(__('Exception message')));
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
        $exception = new \Exception('Exception message');
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
