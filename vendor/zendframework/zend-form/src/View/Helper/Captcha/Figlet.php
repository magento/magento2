<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper\Captcha;

use Zend\Captcha\Figlet as CaptchaAdapter;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;

class Figlet extends AbstractWord
{
    /**
     * Render the captcha
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $captcha = $element->getCaptcha();

        if ($captcha === null || !$captcha instanceof CaptchaAdapter) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has a "captcha" attribute of type Zend\Captcha\Figlet; none found',
                __METHOD__
            ));
        }

        $captcha->generate();

        $figlet = sprintf(
            '<pre>%s</pre>',
            $captcha->getFiglet()->render($captcha->getWord())
        );

        $position     = $this->getCaptchaPosition();
        $separator    = $this->getSeparator();
        $captchaInput = $this->renderCaptchaInputs($element);

        $pattern = '%s%s%s';
        if ($position == self::CAPTCHA_PREPEND) {
            return sprintf($pattern, $captchaInput, $separator, $figlet);
        }

        return sprintf($pattern, $figlet, $separator, $captchaInput);
    }
}
