<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Exception;

/**
 * Helper for rendering a template fragment in its own variable scope; iterates
 * over data provided and renders for each iteration.
 */
class PartialLoop extends Partial
{
    /**
     * Marker to where the pointer is at in the loop
     *
     * @var int
     */
    protected $partialCounter = 0;

    /**
     * The current nesting level
     *
     * @var int
     */
    private $nestingLevel = 0;

    /**
     * Stack with object keys for each nested level
     *
     * @var array indexed by nesting level
     */
    private $objectKeyStack = array(
        0 => null,
    );

    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object.
     *
     * If no arguments are provided, returns object instance.
     *
     * @param  string $name   Name of view script
     * @param  array  $values Variables to populate in the view
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function __invoke($name = null, $values = null)
    {
        if (0 == func_num_args()) {
            return $this;
        }

        // reset the counter if it's called again
        $this->partialCounter = 0;
        $content = '';

        foreach ($this->extractViewVariables($values) as $item) {
            $this->nestObjectKey();

            $this->partialCounter++;
            $content .= parent::__invoke($name, $item);

            $this->unNestObjectKey();
        }

        return $content;
    }

    /**
     * Get the partial counter
     *
     * @return int
     */
    public function getPartialCounter()
    {
        return $this->partialCounter;
    }

    /**
     * Set object key in this loop and any child loop
     *
     * {@inheritDoc}
     *
     * @param string|null $key
     *
     * @return self
     */
    public function setObjectKey($key)
    {
        if (null === $key) {
            unset($this->objectKeyStack[$this->nestingLevel]);
        } else {
            $this->objectKeyStack[$this->nestingLevel] = (string) $key;
        }

        return parent::setObjectKey($key);
    }

    /**
     * Increment nestedLevel and default objectKey to parent's value
     *
     * @return self
     */
    private function nestObjectKey()
    {
        $this->nestingLevel += 1;

        $this->setObjectKey($this->getObjectKey());

        return $this;
    }

    /**
     * Decrement nestedLevel and restore objectKey to parent's value
     *
     * @return self
     */
    private function unNestObjectKey()
    {
        $this->setObjectKey(null);

        $this->nestingLevel -= 1;
        if (isset($this->objectKeyStack[$this->nestingLevel])) {
            $this->objectKey = $this->objectKeyStack[$this->nestingLevel];
        }

        return $this;
    }

    /**
     * @param mixed $values
     *
     * @return array Variables to populate in the view
     */
    private function extractViewVariables($values)
    {
        if ($values instanceof Traversable) {
            return ArrayUtils::iteratorToArray($values, false);
        }

        if (is_array($values)) {
            return $values;
        }

        if (is_object($values) && method_exists($values, 'toArray')) {
            return $values->toArray();
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'PartialLoop helper requires iterable data, %s given',
            is_object($values) ? get_class($values) : gettype($values)
        ));
    }
}
