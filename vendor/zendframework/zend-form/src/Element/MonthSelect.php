<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Element;

use DateTime as PhpDateTime;
use Traversable;
use Zend\Form\Element;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputProviderInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\Regex as RegexValidator;
use Zend\Validator\ValidatorInterface;

class MonthSelect extends Element implements InputProviderInterface, ElementPrepareAwareInterface
{
    /**
     * Select form element that contains values for month
     *
     * @var Select
     */
    protected $monthElement;

    /**
     * Select form element that contains values for year
     *
     * @var Select
     */
    protected $yearElement;

    /**
     * Min year to use for the select (default: current year - 100)
     *
     * @var int
     */
    protected $minYear;

    /**
     * Max year to use for the select (default: current year)
     *
     * @var int
     */
    protected $maxYear;

    /**
     * If set to true, it will generate an empty option for every select (this is mainly needed by most JavaScript
     * libraries to allow to have a placeholder)
     *
     * @var bool
     */
    protected $createEmptyOption = false;

    /**
     * If set to true, view helpers will render delimiters between <select> elements, according to the
     * specified locale
     *
     * @var bool
     */
    protected $renderDelimiters = true;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Constructor. Add two selects elements
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     */
    public function __construct($name = null, $options = array())
    {
        $this->minYear = date('Y') - 100;
        $this->maxYear = date('Y');

        $this->monthElement = new Select('month');
        $this->yearElement = new Select('year');

        parent::__construct($name, $options);
    }

    /**
     * Set element options.
     *
     * Accepted options for MonthSelect:
     *
     * - month_attributes: HTML attributes to be rendered with the month element
     * - year_attributes: HTML attributes to be rendered with the month element
     * - min_year: min year to use in the year select
     * - max_year: max year to use in the year select
     *
     * @param array|Traversable $options
     * @return self
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (isset($options['month_attributes'])) {
            $this->setMonthAttributes($options['month_attributes']);
        }

        if (isset($options['year_attributes'])) {
            $this->setYearAttributes($options['year_attributes']);
        }

        if (isset($options['min_year'])) {
            $this->setMinYear($options['min_year']);
        }

        if (isset($options['max_year'])) {
            $this->setMaxYear($options['max_year']);
        }

        if (isset($options['create_empty_option'])) {
            $this->setShouldCreateEmptyOption($options['create_empty_option']);
        }

        if (isset($options['render_delimiters'])) {
            $this->setShouldRenderDelimiters($options['render_delimiters']);
        }

        return $this;
    }

    /**
     * @return Select
     */
    public function getMonthElement()
    {
        return $this->monthElement;
    }

    /**
     * @return Select
     */
    public function getYearElement()
    {
        return $this->yearElement;
    }

    /**
     * Get both the year and month elements
     *
     * @return array
     */
    public function getElements()
    {
        return array($this->monthElement, $this->yearElement);
    }

    /**
     * Set the month attributes
     *
     * @param  array $monthAttributes
     * @return self
     */
    public function setMonthAttributes(array $monthAttributes)
    {
        $this->monthElement->setAttributes($monthAttributes);
        return $this;
    }

    /**
     * Get the month attributes
     *
     * @return array
     */
    public function getMonthAttributes()
    {
        return $this->monthElement->getAttributes();
    }

    /**
     * Set the year attributes
     *
     * @param  array $yearAttributes
     * @return self
     */
    public function setYearAttributes(array $yearAttributes)
    {
        $this->yearElement->setAttributes($yearAttributes);
        return $this;
    }

    /**
     * Get the year attributes
     *
     * @return array
     */
    public function getYearAttributes()
    {
        return $this->yearElement->getAttributes();
    }

    /**
     * @param  int $minYear
     * @return self
     */
    public function setMinYear($minYear)
    {
        $this->minYear = $minYear;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinYear()
    {
        return $this->minYear;
    }

    /**
     * @param  int $maxYear
     * @return self
     */
    public function setMaxYear($maxYear)
    {
        $this->maxYear = $maxYear;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxYear()
    {
        return $this->maxYear;
    }

    /**
     * @param  bool $createEmptyOption
     * @return self
     */
    public function setShouldCreateEmptyOption($createEmptyOption)
    {
        $this->createEmptyOption = (bool) $createEmptyOption;
        return $this;
    }

    /**
     * @return bool
     */
    public function shouldCreateEmptyOption()
    {
        return $this->createEmptyOption;
    }

    /**
     * @param  bool $renderDelimiters
     * @return self
     */
    public function setShouldRenderDelimiters($renderDelimiters)
    {
        $this->renderDelimiters = (bool) $renderDelimiters;
        return $this;
    }

    /**
     * @return bool
     */
    public function shouldRenderDelimiters()
    {
        return $this->renderDelimiters;
    }

    /**
     * @param mixed $value
     * @return self
     */
    public function setValue($value)
    {
        if ($value instanceof PhpDateTime) {
            $value = array(
                'year'  => $value->format('Y'),
                'month' => $value->format('m')
            );
        }

        $this->yearElement->setValue($value['year']);
        $this->monthElement->setValue($value['month']);
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return sprintf(
            '%s-%s',
            $this->getYearElement()->getValue(),
            $this->getMonthElement()->getValue()
        );
    }

    /**
     * Prepare the form element (mostly used for rendering purposes)
     *
     * @param  FormInterface $form
     * @return void
     */
    public function prepareElement(FormInterface $form)
    {
        $name = $this->getName();
        $this->monthElement->setName($name . '[month]');
        $this->yearElement->setName($name . '[year]');
    }

    /**
     * Get validator
     *
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        return new RegexValidator('/^[0-9]{4}\-(0?[1-9]|1[012])$/');
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInput()}.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        return array(
            'name' => $this->getName(),
            'required' => false,
            'filters' => array(
                array('name' => 'MonthSelect'),
            ),
            'validators' => array(
                $this->getValidator(),
            ),
        );
    }

    /**
     * Clone the element (this is needed by Collection element, as it needs different copies of the elements)
     */
    public function __clone()
    {
        $this->monthElement = clone $this->monthElement;
        $this->yearElement  = clone $this->yearElement;
    }
}
