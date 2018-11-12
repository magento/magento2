<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageCache\Plugin;

use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\TestFramework\Helper\Bootstrap;

class RegisterFormKeyFromCookieTest extends TestCase
{
    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var FrontController
     */
    private $frontController;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->request = $objectManager->get(RequestInterface::class);
        $this->frontController = $objectManager->get(
            FrontController::class
        );
        $this->formKeyValidator = $objectManager->get(FormKeyValidator::class);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testTakenFromCookie()
    {
        if (!Bootstrap::canTestHeaders()) {
            $this->markTestSkipped(
                'Can\'t test dispatch process without sending headers'
            );
        }
        $_SERVER['HTTP_HOST'] = 'localhost';
        $formKey = 'customFormKey';
        $_COOKIE['form_key'] = $formKey;
        $this->request->setMethod(HttpRequest::METHOD_POST);
        $this->request->setParam('form_key', $formKey);
        $this->request->setRequestUri('core/index/index');
        $this->frontController->dispatch($this->request);
        $this->assertTrue($this->formKeyValidator->validate($this->request));
    }
}
