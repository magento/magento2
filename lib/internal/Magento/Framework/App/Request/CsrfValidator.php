<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Validate request for being CSRF protected.
 */
class CsrfValidator implements ValidatorInterface
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @param FormKeyValidator $formKeyValidator
     * @param RedirectFactory $redirectFactory
     * @param AppState $appState
     */
    public function __construct(
        FormKeyValidator $formKeyValidator,
        RedirectFactory $redirectFactory,
        AppState $appState
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->redirectFactory = $redirectFactory;
        $this->appState = $appState;
    }

    /**
     * Validate given request.
     *
     * @param HttpRequest $request
     * @param ActionInterface $action
     *
     * @return bool
     */
    private function validateRequest(
        HttpRequest $request,
        ActionInterface $action
    ): bool {
        $valid = null;
        if ($action instanceof CsrfAwareActionInterface) {
            $valid = $action->validateForCsrf($request);
        }
        if ($valid === null) {
            $valid = !$request->isPost()
                || $request->isXmlHttpRequest()
                || $this->formKeyValidator->validate($request);
        }

        return $valid;
    }

    /**
     * Create exception for when incoming request failed validation.
     *
     * @param HttpRequest $request
     * @param ActionInterface $action
     *
     * @return InvalidRequestException
     */
    private function createException(
        HttpRequest $request,
        ActionInterface $action
    ): InvalidRequestException {
        $exception = null;
        if ($action instanceof CsrfAwareActionInterface) {
            $exception = $action->createCsrfValidationException($request);
        }
        if (!$exception) {
            $response = $this->redirectFactory->create()
                ->setRefererOrBaseUrl()
                ->setHttpResponseCode(302);
            $messages = [
                new Phrase('Invalid Form Key. Please refresh the page.'),
            ];
            $exception = new InvalidRequestException($response, $messages);
        }

        return $exception;
    }

    /**
     * @inheritDoc
     */
    public function validate(
        RequestInterface $request,
        ActionInterface $action
    ): void {
        try {
            $areaCode = $this->appState->getAreaCode();
        } catch (LocalizedException $exception) {
            $areaCode = null;
        }
        if ($request instanceof HttpRequest
            && in_array(
                $areaCode,
                [Area::AREA_FRONTEND, Area::AREA_ADMINHTML],
                true
            )
        ) {
            $valid = $this->validateRequest($request, $action);
            if (!$valid) {
                throw $this->createException($request, $action);
            }
        }
    }
}
