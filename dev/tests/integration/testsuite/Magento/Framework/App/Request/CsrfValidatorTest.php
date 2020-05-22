<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Request\Http as HttpRequest;
use Laminas\Stdlib\Parameters;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\Response\HttpFactory as HttpResponseFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CsrfValidatorTest extends TestCase
{
    private const AWARE_URL = 'test/1';

    private const AWARE_VALIDATION_PARAM = 'test_param';

    private const AWARE_MESSAGE = 'custom validation failed';

    /**
     * @var ActionInterface
     */
    private $mockUnawareAction;

    /**
     * @var ActionInterface
     */
    private $mockAwareAction;

    /**
     * @var CsrfValidator
     */
    private $validator;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var HttpResponseFactory
     */
    private $httpResponseFactory;

    /**
     * @return ActionInterface
     */
    private function createUnawareAction(): ActionInterface
    {
        return new class implements ActionInterface {
            /**
             * @inheritDoc
             */
            public function execute()
            {
                throw new NotFoundException(new Phrase('Not implemented'));
            }
        };
    }

    /**
     * @return ActionInterface
     */
    private function createAwareAction(): ActionInterface
    {
        $u = self::AWARE_URL;
        $m = self::AWARE_MESSAGE;
        $p = self::AWARE_VALIDATION_PARAM;

        return new class($u, $m, $p) implements CsrfAwareActionInterface {
            /**
             * @var string
             */
            private $url;

            /**
             * @var string
             */
            private $message;

            /**
             * @var string
             */
            private $param;

            /**
             * @param string $url
             * @param string $message
             * @param string $param
             */
            public function __construct(
                string $url,
                string $message,
                string $param
            ) {
                $this->url = $url;
                $this->message = $message;
                $this->param = $param;
            }

            /**
             * @inheritDoc
             */
            public function execute()
            {
                throw new NotFoundException(new Phrase('Not implemented'));
            }

            /**
             * @inheritDoc
             */
            public function createCsrfValidationException(
                RequestInterface $request
            ): ?InvalidRequestException {
                /** @var RedirectFactory $redirectFactory */
                $redirectFactory = Bootstrap::getObjectManager()
                    ->get(RedirectFactory::class);
                $redirect = $redirectFactory->create();
                $redirect->setUrl($this->url);

                return new InvalidRequestException(
                    $redirect,
                    [new Phrase($this->message)]
                );
            }

            /**
             * @inheritDoc
             */
            public function validateForCsrf(RequestInterface $request): ?bool
            {
                return (bool)$request->getParam($this->param);
            }
        };
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->request = $objectManager->get(HttpRequest::class);
        $this->validator = $objectManager->get(CsrfValidator::class);
        $this->mockUnawareAction = $this->createUnawareAction();
        $this->mockAwareAction = $this->createAwareAction();
        $this->formKey = $objectManager->get(FormKey::class);
        $this->httpResponseFactory = $objectManager->get(
            HttpResponseFactory::class
        );
    }

    /**
     * @magentoAppArea global
     */
    public function testValidateInWrongArea()
    {
        $this->request->setMethod(HttpRequest::METHOD_POST);
        $this->validator->validate(
            $this->request,
            $this->mockUnawareAction
        );
    }

    /**
     * @magentoAppArea frontend
     */
    public function testValidateWithValidKey()
    {
        $this->request->setPost(
            new Parameters(['form_key' => $this->formKey->getFormKey()])
        );
        $this->request->setMethod(HttpRequest::METHOD_POST);

        $this->validator->validate(
            $this->request,
            $this->mockUnawareAction
        );
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testValidateWithInvalidKey()
    {
        $this->expectException(\Magento\Framework\App\Request\InvalidRequestException::class);

        $this->request->setPost(
            new Parameters(['form_key' => $this->formKey->getFormKey() .'1'])
        );
        $this->request->setMethod(HttpRequest::METHOD_POST);

        $this->validator->validate(
            $this->request,
            $this->mockUnawareAction
        );
    }

    /**
     * @magentoAppArea frontend
     */
    public function testValidateInvalidWithAwareAction()
    {
        $this->request->setMethod(HttpRequest::METHOD_POST);

        /** @var InvalidRequestException|null $caught */
        $caught = null;
        try {
            $this->validator->validate(
                $this->request,
                $this->mockAwareAction
            );
        } catch (InvalidRequestException $exception) {
            $caught = $exception;
        }

        $this->assertNotNull($caught);
        $this->assertInstanceOf(Redirect::class, $caught->getReplaceResult());
        /** @var HttpResponse $response */
        $response = $this->httpResponseFactory->create();
        $caught->getReplaceResult()->renderResult($response);
        $this->assertStringContainsString(
            self::AWARE_URL,
            $response->getHeaders()->toString()
        );
        $this->assertCount(1, $caught->getMessages());
        $this->assertEquals(
            self::AWARE_MESSAGE,
            $caught->getMessages()[0]->getText()
        );
    }

    /**
     * @magentoAppArea frontend
     */
    public function testValidateValidWithAwareAction()
    {
        $this->request->setMethod(HttpRequest::METHOD_POST);
        $this->request->setPost(
            new Parameters([self::AWARE_VALIDATION_PARAM => '1'])
        );

        $this->validator->validate(
            $this->request,
            $this->mockAwareAction
        );
    }
}
