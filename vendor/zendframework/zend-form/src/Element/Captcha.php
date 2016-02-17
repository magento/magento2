<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Element;

use Traversable;
use Zend\Captcha as ZendCaptcha;
use Zend\Form\Element;
use Zend\Form\Exception;
use Zend\InputFilter\InputProviderInterface;

class Captcha extends Element implements InputProviderInterface
{
    /**
     * @var \Zend\Captcha\AdapterInterface
     */
    protected $captcha;

    /**
     * Accepted options for Captcha:
     * - captcha: a valid Zend\Captcha\AdapterInterface
     *
     * @param array|Traversable $options
     * @return Captcha
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($this->options['captcha'])) {
            $this->setCaptcha($this->options['captcha']);
        }

        return $this;
    }

    /**
     * Set captcha
     *
     * @param  array|ZendCaptcha\AdapterInterface $captcha
     * @throws Exception\InvalidArgumentException
     * @return Captcha
     */
    public function setCaptcha($captcha)
    {
        if (is_array($captcha) || $captcha instanceof Traversable) {
            $captcha = ZendCaptcha\Factory::factory($captcha);
        } elseif (!$captcha instanceof ZendCaptcha\AdapterInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects either a Zend\Captcha\AdapterInterface or specification to pass to Zend\Captcha\Factory; received "%s"',
                __METHOD__,
                (is_object($captcha) ? get_class($captcha) : gettype($captcha))
            ));
        }
        $this->captcha = $captcha;

        return $this;
    }

    /**
     * Retrieve captcha (if any)
     *
     * @return null|ZendCaptcha\AdapterInterface
     */
    public function getCaptcha()
    {
        return $this->captcha;
    }

    /**
     * Provide default input rules for this element
     *
     * Attaches the captcha as a validator.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        $spec = array(
            'name' => $this->getName(),
            'required' => true,
            'filters' => array(
                array('name' => 'Zend\Filter\StringTrim'),
            ),
        );

        // Test that we have a captcha before adding it to the spec
        $captcha = $this->getCaptcha();
        if ($captcha instanceof ZendCaptcha\AdapterInterface) {
            $spec['validators'] = array($captcha);
        }

        return $spec;
    }
}
