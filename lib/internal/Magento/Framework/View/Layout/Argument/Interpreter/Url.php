<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\UrlInterface;

/**
 * Interpreter that builds URL by input path and optional parameters
 * @since 2.0.0
 */
class Url implements InterpreterInterface
{
    /**
     * @var UrlInterface
     * @since 2.0.0
     */
    private $urlResolver;

    /**
     * @var NamedParams
     * @since 2.0.0
     */
    private $paramsInterpreter;

    /**
     * @param UrlInterface $urlResolver
     * @param NamedParams $paramsInterpreter
     * @since 2.0.0
     */
    public function __construct(UrlInterface $urlResolver, NamedParams $paramsInterpreter)
    {
        $this->urlResolver = $urlResolver;
        $this->paramsInterpreter = $paramsInterpreter;
    }

    /**
     * {@inheritdoc}
     * @return string
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function evaluate(array $data)
    {
        if (!isset($data['path'])) {
            throw new \InvalidArgumentException('URL path is missing.');
        }
        $urlPath = $data['path'];
        $urlParams = $this->paramsInterpreter->evaluate($data);
        return $this->urlResolver->getUrl($urlPath, $urlParams);
    }
}
