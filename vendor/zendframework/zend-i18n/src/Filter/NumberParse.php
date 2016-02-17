<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\Filter;

use NumberFormatter;
use Traversable;
use Zend\I18n\Exception;
use Zend\Stdlib\ErrorHandler;

class NumberParse extends AbstractLocale
{
    protected $options = array(
        'locale' => null,
        'style'  => NumberFormatter::DEFAULT_STYLE,
        'type'   => NumberFormatter::TYPE_DOUBLE
    );

    /**
     * @var NumberFormatter
     */
    protected $formatter = null;

    /**
     * @param array|Traversable|string|null $localeOrOptions
     * @param int  $style
     * @param int  $type
     */
    public function __construct(
        $localeOrOptions = null,
        $style = NumberFormatter::DEFAULT_STYLE,
        $type = NumberFormatter::TYPE_DOUBLE
    ) {
        parent::__construct();
        if ($localeOrOptions !== null) {
            if ($localeOrOptions instanceof Traversable) {
                $localeOrOptions = iterator_to_array($localeOrOptions);
            }

            if (!is_array($localeOrOptions)) {
                $this->setLocale($localeOrOptions);
                $this->setStyle($style);
                $this->setType($type);
            } else {
                $this->setOptions($localeOrOptions);
            }
        }
    }

    /**
     * @param  string|null $locale
     * @return NumberFormat
     */
    public function setLocale($locale = null)
    {
        $this->options['locale'] = $locale;
        $this->formatter = null;
        return $this;
    }

    /**
     * @param  int $style
     * @return NumberFormat
     */
    public function setStyle($style)
    {
        $this->options['style'] = (int) $style;
        $this->formatter = null;
        return $this;
    }

    /**
     * @return int
     */
    public function getStyle()
    {
        return $this->options['style'];
    }

    /**
     * @param  int $type
     * @return NumberFormat
     */
    public function setType($type)
    {
        $this->options['type'] = (int) $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * @param  NumberFormatter $formatter
     * @return NumberFormat
     */
    public function setFormatter(NumberFormatter $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * @return NumberFormatter
     * @throws Exception\RuntimeException
     */
    public function getFormatter()
    {
        if ($this->formatter === null) {
            $formatter = NumberFormatter::create($this->getLocale(), $this->getStyle());
            if (!$formatter) {
                throw new Exception\RuntimeException(
                    'Can not create NumberFormatter instance; ' . intl_get_error_message()
                );
            }

            $this->formatter = $formatter;
        }

        return $this->formatter;
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * @see    Zend\Filter\FilterInterface::filter()
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (!is_int($value)
            && !is_float($value)
        ) {
            ErrorHandler::start();

            $result = $this->getFormatter()->parse(
                $value,
                $this->getType()
            );

            ErrorHandler::stop();

            if (false !== $result) {
                return $result;
            }
        }

        return $value;
    }
}
