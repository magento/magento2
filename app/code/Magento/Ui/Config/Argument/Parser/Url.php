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
 */
class Url implements InterpreterInterface
{
    /**
     * @var UrlInterface
     */
    private $urlResolver;

    /**
     * @var NamedParams
     */
    private $paramsInterpreter;

    /**
     * @param UrlFactory $urlResolverFactory
     * @param NamedParams $paramsInterpreter
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
