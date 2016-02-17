<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper\Captcha;

use Zend\Captcha\Image as CaptchaAdapter;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;

class Image extends AbstractWord
{
    /**
     * Render the captcha
     *
     * @param  ElementInterface          $element
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $captcha = $element->getCaptcha();

        if ($captcha === null || !$captcha instanceof CaptchaAdapter) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has a "captcha" attribute of type Zend\Captcha\Image; none found',
                __METHOD__
            ));
        }

        $captcha->generate();

        $imgAttributes = array(
            'width'  => $captcha->getWidth(),
            'height' => $captcha->getHeight(),
            'alt'    => $captcha->getImgAlt(),
            'src'    => $captcha->getImgUrl() . $captcha->getId() . $captcha->getSuffix(),
        );

        if ($element->hasAttribute('id')) {
            $imgAttributes['id'] = $element->getAttribute('id') . '-image';
        }

        $closingBracket = $this->getInlineClosingBracket();
        $img = sprintf(
            '<img %s%s',
            $this->createAttributesString($imgAttributes),
            $closingBracket
        );

        $position     = $this->getCaptchaPosition();
        $separator    = $this->getSeparator();
        $captchaInput = $this->renderCaptchaInputs($element);

        $pattern = '%s%s%s';
        if ($position == self::CAPTCHA_PREPEND) {
            return sprintf($pattern, $captchaInput, $separator, $img);
        }

        return sprintf($pattern, $img, $separator, $captchaInput);
    }
}
