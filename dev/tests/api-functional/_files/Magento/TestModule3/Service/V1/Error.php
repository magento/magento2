<?php
/**
 * Implementation of a test service for error handling testing
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule3\Service\V1;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestModule3\Service\V1\Entity\Parameter;
use Magento\TestModule3\Service\V1\Entity\ParameterFactory;

class Error implements \Magento\TestModule3\Service\V1\ErrorInterface
{
    /**
     * @var ParameterFactory
     */
    protected $parameterFactory;

    /**
     * @param ParameterFactory $parameterFactory
     */
    public function __construct(ParameterFactory $parameterFactory)
    {
        $this->parameterFactory = $parameterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function success()
    {
        return $this->parameterFactory->create()->setName('id')->setValue('a good id');
    }

    /**
     * {@inheritdoc}
     */
    public function resourceNotFoundException()
    {
        throw new NoSuchEntityException(
            __(
                'Resource with ID "%1" not found.',
                'resourceY'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serviceException()
    {
        throw new LocalizedException(__('Generic service exception %1', 3456));
    }

    /**
     * {@inheritdoc}
     */
    public function authorizationException()
    {
        throw new AuthorizationException(__('Consumer is not authorized to access %1', 'resourceN'));
    }

    /**
     * {@inheritdoc}
     */
    public function webapiException()
    {
        throw new \Magento\Framework\Webapi\Exception(
            __('Service not found'),
            5555,
            \Magento\Framework\Webapi\Exception::HTTP_NOT_FOUND
        );
    }

    /**
     * {@inheritdoc}
     */
    public function otherException()
    {
        throw new \Exception('Non service exception', 5678);
    }

    /**
     * {@inheritdoc}
     */
    public function returnIncompatibleDataType()
    {
        return "incompatibleDataType";
    }

    /**
     * {@inheritdoc}
     */
    public function inputException($wrappedErrorParameters)
    {
        $exception = new InputException();
        if ($wrappedErrorParameters) {
            foreach ($wrappedErrorParameters as $error) {
                $exception->addError(
                    __(
                        'Invalid value of "%value" provided for the %fieldName field.',
                        ['fieldName' => $error->getFieldName(), 'value' => $error->getValue()]
                    )
                );
            }
        }
        throw $exception;
    }
}
