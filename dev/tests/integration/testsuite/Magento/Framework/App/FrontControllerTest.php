<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Request\ValidatorInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\ResultInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea frontend
 */
class FrontControllerTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var FrontController
     */
    protected $_model;

    /**
     * @var ValidatorInterface
     */
    private $fakeRequestValidator;

    /**
     * @return ValidatorInterface
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function createRequestValidator(): ValidatorInterface
    {
        if (!$this->fakeRequestValidator) {
            $this->fakeRequestValidator = new class implements ValidatorInterface {
                /**
                 * @var bool
                 */
                public $valid;

                /**
                 * @inheritDoc
                 */
                public function validate(
                    RequestInterface $request,
                    ActionInterface $action
                ): void {
                    if (!$this->valid) {
                        throw new InvalidRequestException(new NotFoundException(new Phrase('Not found')));
                    }
                }
            };
        }

        return $this->fakeRequestValidator;
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->create(
            FrontController::class,
            ['requestValidator' => $this->createRequestValidator()]
        );
    }

    /**
     * Test dispatching an empty action.
     */
    public function testDispatch()
    {
        if (!Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test dispatch process without sending headers');
        }
        $this->fakeRequestValidator->valid = true;
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->_objectManager->get(State::class)->setAreaCode('frontend');
        $request = $this->_objectManager->get(HttpRequest::class);
        /* empty action */
        $request->setRequestUri('core/index/index');
        $this->assertInstanceOf(
            ResultInterface::class,
            $this->_model->dispatch($request)
        );
    }

    /**
     * Test request validator invalidating given request.
     */
    public function testInvalidRequest()
    {
        if (!Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test dispatch process without sending headers');
        }
        $this->fakeRequestValidator->valid = false;
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->_objectManager->get(State::class)->setAreaCode('frontend');
        $request = $this->_objectManager->get(HttpRequest::class);
        /* empty action */
        $request->setRequestUri('core/index/index');
        $this->assertInstanceOf(
            ResultInterface::class,
            $this->_model->dispatch($request)
        );
    }
}
