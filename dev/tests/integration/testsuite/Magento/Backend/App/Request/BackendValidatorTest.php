<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backend\App\Request;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;
use Magento\TestFramework\Request;
use Magento\TestFramework\Response;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Bootstrap as TestBootstrap;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Zend\Stdlib\Parameters;
use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\App\Response\HttpFactory as HttpResponseFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackendValidatorTest extends TestCase
{
    private const AWARE_VALIDATION_PARAM = 'test_param';

    private const AWARE_LOCATION_VALUE = 'test1';

    private const CSRF_AWARE_MESSAGE = 'csrf_aware';

    /**
     * @var ActionInterface
     */
    private $mockUnawareAction;

    /**
     * @var AbstractAction
     */
    private $mockAwareAction;

    /**
     * @var BackendValidator
     */
    private $validator;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var BackendUrl
     */
    private $url;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var CsrfAwareActionInterface
     */
    private $mockCsrfAwareAction;

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
     * @return AbstractAction
     */
    private function createAwareAction(): AbstractAction
    {
        $l = self::AWARE_LOCATION_VALUE;
        $p = self::AWARE_VALIDATION_PARAM;

        return new class($l, $p) extends AbstractAction{

            /**
             * @var string
             */
            private $locationValue;

            /**
             * @var string
             */
            private $param;

            /**
             * @param string $locationValue
             * @param string $param
             */
            public function __construct(
                string $locationValue,
                string $param
            ) {
                parent::__construct(
                    Bootstrap::getObjectManager()->get(Context::class)
                );
                $this->locationValue= $locationValue;
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
            public function _processUrlKeys()
            {
                if ($this->_request->getParam($this->param)) {
                    return true;
                } else {
                    /** @var Response $response */
                    $response = $this->_response;
                    $response->setHeader('Location', $this->locationValue);

                    return false;
                }
            }
        };
    }

    /**
     * @return CsrfAwareActionInterface
     */
    private function createCsrfAwareAction(): CsrfAwareActionInterface
    {
        $r = Bootstrap::getObjectManager()
            ->get(ResponseInterface::class);
        $m = self::CSRF_AWARE_MESSAGE;

        return new class ($r, $m) implements CsrfAwareActionInterface {

            /**
             * @var ResponseInterface
             */
            private $response;

            /**
             * @var string
             */
            private $message;

            /**
             * @param ResponseInterface $response
             * @param string $message
             */
            public function __construct(
                ResponseInterface $response,
                string $message
            ) {
                $this->response = $response;
                $this->message = $message;
            }

            /**
             * @inheritDoc
             */
            public function execute()
            {
                return $this->response;
            }

            /**
             * @inheritDoc
             */
            public function createCsrfValidationException(
                RequestInterface $request
            ): ?InvalidRequestException {
                return new InvalidRequestException(
                    $this->response,
                    [new Phrase($this->message)]
                );
            }

            /**
             * @inheritDoc
             */
            public function validateForCsrf(RequestInterface $request): ?bool
            {
                return false;
            }

        };
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->request = $objectManager->get(RequestInterface::class);
        $this->validator = $objectManager->get(BackendValidator::class);
        $this->mockUnawareAction = $this->createUnawareAction();
        $this->mockAwareAction = $this->createAwareAction();
        $this->formKey = $objectManager->get(FormKey::class);
        $this->url = $objectManager->get(BackendUrl::class);
        $this->auth = $objectManager->get(Auth::class);
        $this->mockCsrfAwareAction = $this->createCsrfAwareAction();
        $this->httpResponseFactory = $objectManager->get(
            HttpResponseFactory::class
        );
    }

    /**
     * @magentoConfigFixture admin/security/use_form_key 1
     * @magentoAppArea adminhtml
     */
    public function testValidateWithValidKey()
    {
        $this->request->setMethod(HttpRequest::METHOD_GET);
        $this->auth->login(
            TestBootstrap::ADMIN_NAME,
            TestBootstrap::ADMIN_PASSWORD
        );
        $this->request->setParams([
            BackendUrl::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey(),
        ]);

        $this->validator->validate(
            $this->request,
            $this->mockUnawareAction
        );
    }

    /**
     * @expectedException \Magento\Framework\App\Request\InvalidRequestException
     *
     * @magentoConfigFixture admin/security/use_form_key 1
     * @magentoAppArea adminhtml
     */
    public function testValidateWithInvalidKey()
    {
        $invalidKey = $this->url->getSecretKey() .'Invalid';
        $this->request->setParams([
            BackendUrl::SECRET_KEY_PARAM_NAME => $invalidKey,
        ]);
        $this->request->setMethod(HttpRequest::METHOD_GET);
        $this->auth->login(
            TestBootstrap::ADMIN_NAME,
            TestBootstrap::ADMIN_PASSWORD
        );

        $this->validator->validate(
            $this->request,
            $this->mockUnawareAction
        );
    }

    /**
     * @expectedException \Magento\Framework\App\Request\InvalidRequestException
     *
     * @magentoConfigFixture admin/security/use_form_key 0
     * @magentoAppArea adminhtml
     */
    public function testValidateWithInvalidFormKey()
    {
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
     * @magentoConfigFixture admin/security/use_form_key 0
     * @magentoAppArea adminhtml
     */
    public function testValidateInvalidWithAwareAction()
    {
        $this->request->setParams([self::AWARE_VALIDATION_PARAM => '']);

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
        /** @var Response $response */
        $response = $caught->getReplaceResult();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(
            self::AWARE_LOCATION_VALUE,
            $response->getHeader('Location')->getFieldValue()
        );
        $this->assertNull($caught->getMessages());
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testValidateValidWithAwareAction()
    {
        $this->request->setParams(
            [self::AWARE_VALIDATION_PARAM => '1']
        );

        $this->validator->validate(
            $this->request,
            $this->mockAwareAction
        );
    }

    /**
     * @magentoConfigFixture admin/security/use_form_key 1
     * @magentoAppArea adminhtml
     */
    public function testValidateWithCsrfAwareAction()
    {
        //Setting up request that would be valid for default validation.
        $this->request->setMethod(HttpRequest::METHOD_GET);
        $this->auth->login(
            TestBootstrap::ADMIN_NAME,
            TestBootstrap::ADMIN_PASSWORD
        );
        $this->request->setParams([
            BackendUrl::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey(),
        ]);

        /** @var InvalidRequestException|null $caught */
        $caught = null;
        try {
            $this->validator->validate(
                $this->request,
                $this->mockCsrfAwareAction
            );
        } catch (InvalidRequestException $exception) {
            $caught = $exception;
        }

        //Checking that custom validation was called and invalidated
        //valid request.
        $this->assertNotNull($caught);
        $this->assertCount(1, $caught->getMessages());
        $this->assertEquals(
            self::CSRF_AWARE_MESSAGE,
            $caught->getMessages()[0]->getText()
        );
    }

    public function testInvalidAjaxRequest()
    {
        //Setting up AJAX request with invalid secret key.
        $this->request->setMethod(HttpRequest::METHOD_GET);
        $this->auth->login(
            TestBootstrap::ADMIN_NAME,
            TestBootstrap::ADMIN_PASSWORD
        );
        $this->request->setParams([
            BackendUrl::SECRET_KEY_PARAM_NAME => 'invalid',
            'isAjax' => '1'
        ]);

        /** @var InvalidRequestException|null $caught */
        $caught = null;
        try {
            $this->validator->validate(
                $this->request,
                $this->mockUnawareAction
            );
        } catch (InvalidRequestException $exception) {
            $caught = $exception;
        }

        $this->assertNotNull($caught);
        $this->assertInstanceOf(
            ResultInterface::class,
            $caught->getReplaceResult()
        );
        /** @var ResultInterface $result */
        $result = $caught->getReplaceResult();
        /** @var HttpResponse $response */
        $response = $this->httpResponseFactory->create();
        $result->renderResult($response);
        $this->assertEmpty($response->getBody());
        $this->assertEquals(401, $response->getHttpResponseCode());
    }
}
