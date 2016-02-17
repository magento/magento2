<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper;

use Zend\Captcha\AdapterInterface as CaptchaAdapter;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;

class FormCaptcha extends AbstractHelper
{
    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface $element
     * @return string|FormCaptcha
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Render a form captcha for an element
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException if the element does not compose a captcha, or the renderer does not implement plugin()
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $captcha = $element->getCaptcha();

        if ($captcha === null || !$captcha instanceof CaptchaAdapter) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has a "captcha" attribute implementing Zend\Captcha\AdapterInterface; none found',
                __METHOD__
            ));
        }

        $helper  = $captcha->getHelperName();

        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the renderer implements plugin(); it does not',
                __METHOD__
            ));
        }

        $helper = $renderer->plugin($helper);
        return $helper($element);
    }
}
