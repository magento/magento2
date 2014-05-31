<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Layout\Argument\Interpreter;

use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter that builds URL by input path and optional parameters
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
     * @param UrlInterface $urlResolver
     * @param NamedParams $paramsInterpreter
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
