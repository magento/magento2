<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Argument\Parser;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Layout\Argument\Interpreter\NamedParams;

/**
 * Interpreter that builds URL by input path and optional parameters
 *
 * Used isolated instance of UrlInterface since the shared instance has global state causing issues.
 * @since 2.2.0
 */
class Url implements InterpreterInterface
{
    /**
     * @var UrlInterface
     * @since 2.2.0
     */
    private $urlResolver;

    /**
     * @var NamedParams
     * @since 2.2.0
     */
    private $paramsInterpreter;

    /**
     * @param UrlFactory $urlResolverFactory
     * @param NamedParams $paramsInterpreter
     * @since 2.2.0
     */
    public function __construct(UrlFactory $urlResolverFactory, NamedParams $paramsInterpreter)
    {
        $this->urlResolver = $urlResolverFactory->create();
        $this->paramsInterpreter = $paramsInterpreter;
    }

    /**
     * {@inheritdoc}
     * @return string
     * @throws \InvalidArgumentException
     * @since 2.2.0
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
