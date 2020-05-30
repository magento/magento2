<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\ViewModel\Template\Preview;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class Form
 */
class Form implements ArgumentInterface
{
    private $expectedParamsGetRequest = [
        'id'
    ];

    private $expectedParamsPostRequest = [
        'text',
        'type',
        'styles'
    ];

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Gets the fields to be included in the email preview form.
     *
     * @return array
     * @throws LocalizedException
     */
    public function getFormFields()
    {
        $params = $fields = [];
        $method = $this->request->getMethod();

        if ($method === 'GET') {
            $params = $this->expectedParamsGetRequest;
        } elseif ($method === 'POST') {
            $params = $this->expectedParamsPostRequest;
        }

        foreach ($params as $paramName) {
            $fieldValue = $this->request->getParam($paramName);
            if ($fieldValue === null) {
                throw new LocalizedException(
                    __("Missing expected parameter \"$paramName\" while attempting to generate template preview.")
                );
            }
            $fields[$paramName] = $fieldValue;
        }

        return $fields;
    }
}
