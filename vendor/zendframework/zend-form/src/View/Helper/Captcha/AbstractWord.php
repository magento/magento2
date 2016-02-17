<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper\Captcha;

use Zend\Captcha\AdapterInterface as CaptchaAdapter;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\FormInput;

abstract class AbstractWord extends FormInput
{
    const CAPTCHA_APPEND  = 'append';
    const CAPTCHA_PREPEND = 'prepend';

    /**
     * @var FormInput
     */
    protected $inputHelper;

    /**
     * @var string
     */
    protected $captchaPosition = self::CAPTCHA_APPEND;

    /**
     * Separator string for captcha and inputs
     *
     * @var string
     */
    protected $separator = '';

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Render captcha form elements for the given element
     *
     * Creates and returns:
     * - Hidden input with captcha identifier (name[id])
     * - Text input for entering captcha value (name[input])
     *
     * More specific renderers will consume this and render it.
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    protected function renderCaptchaInputs(ElementInterface $element)
    {
        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $attributes = $element->getAttributes();
        $captcha = $element->getCaptcha();

        if ($captcha === null || !$captcha instanceof CaptchaAdapter) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has a "captcha" attribute implementing Zend\Captcha\AdapterInterface; none found',
                __METHOD__
            ));
        }

        $hidden = $this->renderCaptchaHidden($captcha, $attributes);
        $input  = $this->renderCaptchaInput($captcha, $attributes);

        return $hidden . $input;
    }

    /**
     * Render the hidden input with the captcha identifier
     *
     * @param  CaptchaAdapter $captcha
     * @param  array          $attributes
     * @return string
     */
    protected function renderCaptchaHidden(CaptchaAdapter $captcha, array $attributes)
    {
        $attributes['type']  = 'hidden';
        $attributes['name'] .= '[id]';

        if (isset($attributes['id'])) {
            $attributes['id'] .= '-hidden';
        }

        if (method_exists($captcha, 'getId')) {
            $attributes['value'] = $captcha->getId();
        } elseif (array_key_exists('value', $attributes)) {
            if (is_array($attributes['value']) && array_key_exists('id', $attributes['value'])) {
                $attributes['value'] = $attributes['value']['id'];
            }
        }
        $closingBracket      = $this->getInlineClosingBracket();
        $hidden              = sprintf(
            '<input %s%s',
            $this->createAttributesString($attributes),
            $closingBracket
        );

        return $hidden;
    }

    /**
     * Render the input for capturing the captcha value from the client
     *
     * @param  CaptchaAdapter $captcha
     * @param  array          $attributes
     * @return string
     */
    protected function renderCaptchaInput(CaptchaAdapter $captcha, array $attributes)
    {
        $attributes['type']  = 'text';
        $attributes['name'] .= '[input]';
        if (array_key_exists('value', $attributes)) {
            unset($attributes['value']);
        }
        $closingBracket      = $this->getInlineClosingBracket();
        $input               = sprintf(
            '<input %s%s',
            $this->createAttributesString($attributes),
            $closingBracket
        );

        return $input;
    }

    /**
     * Set value for captchaPosition
     *
     * @param  mixed $captchaPosition
     * @throws Exception\InvalidArgumentException
     * @return AbstractWord
     */
    public function setCaptchaPosition($captchaPosition)
    {
        $captchaPosition = strtolower($captchaPosition);
        if (!in_array($captchaPosition, array(self::CAPTCHA_APPEND, self::CAPTCHA_PREPEND))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects either %s::CAPTCHA_APPEND or %s::CAPTCHA_PREPEND; received "%s"',
                __METHOD__,
                __CLASS__,
                __CLASS__,
                (string) $captchaPosition
            ));
        }
        $this->captchaPosition = $captchaPosition;

        return $this;
    }

    /**
     * Get position of captcha
     *
     * @return string
     */
    public function getCaptchaPosition()
    {
        return $this->captchaPosition;
    }

    /**
     * Set separator string for captcha and inputs
     *
     * @param  string $separator
     * @return AbstractWord
     */
    public function setSeparator($separator)
    {
        $this->separator = (string) $separator;
        return $this;
    }

    /**
     * Get separator for captcha and inputs
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }
}
