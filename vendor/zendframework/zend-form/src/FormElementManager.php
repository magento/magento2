<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\InitializableInterface;

/**
 * Plugin manager implementation for form elements.
 *
 * Enforces that elements retrieved are instances of ElementInterface.
 */
class FormElementManager extends AbstractPluginManager
{
    /**
     * Default set of helpers
     *
     * @var array
     */
    protected $invokableClasses = array(
        'button'        => 'Zend\Form\Element\Button',
        'captcha'       => 'Zend\Form\Element\Captcha',
        'checkbox'      => 'Zend\Form\Element\Checkbox',
        'collection'    => 'Zend\Form\Element\Collection',
        'color'         => 'Zend\Form\Element\Color',
        'csrf'          => 'Zend\Form\Element\Csrf',
        'date'          => 'Zend\Form\Element\Date',
        'dateselect'    => 'Zend\Form\Element\DateSelect',
        'datetime'      => 'Zend\Form\Element\DateTime',
        'datetimelocal' => 'Zend\Form\Element\DateTimeLocal',
        'datetimeselect' => 'Zend\Form\Element\DateTimeSelect',
        'element'       => 'Zend\Form\Element',
        'email'         => 'Zend\Form\Element\Email',
        'fieldset'      => 'Zend\Form\Fieldset',
        'file'          => 'Zend\Form\Element\File',
        'form'          => 'Zend\Form\Form',
        'hidden'        => 'Zend\Form\Element\Hidden',
        'image'         => 'Zend\Form\Element\Image',
        'month'         => 'Zend\Form\Element\Month',
        'monthselect'   => 'Zend\Form\Element\MonthSelect',
        'multicheckbox' => 'Zend\Form\Element\MultiCheckbox',
        'number'        => 'Zend\Form\Element\Number',
        'password'      => 'Zend\Form\Element\Password',
        'radio'         => 'Zend\Form\Element\Radio',
        'range'         => 'Zend\Form\Element\Range',
        'select'        => 'Zend\Form\Element\Select',
        'submit'        => 'Zend\Form\Element\Submit',
        'text'          => 'Zend\Form\Element\Text',
        'textarea'      => 'Zend\Form\Element\Textarea',
        'time'          => 'Zend\Form\Element\Time',
        'url'           => 'Zend\Form\Element\Url',
        'week'          => 'Zend\Form\Element\Week',
    );

    /**
     * Don't share form elements by default
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * @param ConfigInterface $configuration
     */
    public function __construct(ConfigInterface $configuration = null)
    {
        parent::__construct($configuration);

        $this->addInitializer(array($this, 'injectFactory'));
        $this->addInitializer(array($this, 'callElementInit'), false);
    }

    /**
     * Inject the factory to any element that implements FormFactoryAwareInterface
     *
     * @param $element
     */
    public function injectFactory($element)
    {
        if ($element instanceof FormFactoryAwareInterface) {
            $factory = $element->getFormFactory();
            $factory->setFormElementManager($this);

            if ($this->serviceLocator instanceof ServiceLocatorInterface
                && $this->serviceLocator->has('InputFilterManager')
            ) {
                $inputFilters = $this->serviceLocator->get('InputFilterManager');
                $factory->getInputFilterFactory()->setInputFilterManager($inputFilters);
            }
        }
    }

    /**
     * Call init() on any element that implements InitializableInterface
     *
     * @internal param $element
     */
    public function callElementInit($element)
    {
        if ($element instanceof InitializableInterface) {
            $element->init();
        }
    }

    /**
     * Validate the plugin
     *
     * Checks that the element is an instance of ElementInterface
     *
     * @param  mixed $plugin
     * @throws Exception\InvalidElementException
     * @return void
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof ElementInterface) {
            return; // we're okay
        }

        throw new Exception\InvalidElementException(sprintf(
            'Plugin of type %s is invalid; must implement Zend\Form\ElementInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }

    /**
     * Retrieve a service from the manager by name
     *
     * Allows passing an array of options to use when creating the instance.
     * createFromInvokable() will use these and pass them to the instance
     * constructor if not null and a non-empty array.
     *
     * @param  string $name
     * @param  string|array $options
     * @param  bool $usePeeringServiceManagers
     * @return object
     */
    public function get($name, $options = array(), $usePeeringServiceManagers = true)
    {
        if (is_string($options)) {
            $options = array('name' => $options);
        }
        return parent::get($name, $options, $usePeeringServiceManagers);
    }

    /**
     * Attempt to create an instance via an invokable class
     *
     * Overrides parent implementation by passing $creationOptions to the
     * constructor, if non-null.
     *
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return null|\stdClass
     * @throws ServiceNotCreatedException If resolved class does not exist
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        $invokable = $this->invokableClasses[$canonicalName];

        if (null === $this->creationOptions
            || (is_array($this->creationOptions) && empty($this->creationOptions))
        ) {
            $instance = new $invokable();
        } else {
            if (isset($this->creationOptions['name'])) {
                $name = $this->creationOptions['name'];
            } else {
                $name = $requestedName;
            }

            if (isset($this->creationOptions['options'])) {
                $options = $this->creationOptions['options'];
            } else {
                $options = $this->creationOptions;
            }

            $instance = new $invokable($name, $options);
        }

        return $instance;
    }
}
