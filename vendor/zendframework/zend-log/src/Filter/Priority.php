<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Filter;

use Traversable;
use Zend\Log\Exception;

class Priority implements FilterInterface
{
    /**
     * @var int
     */
    protected $priority;

    /**
     * @var string
     */
    protected $operator;

    /**
     * Filter logging by $priority. By default, it will accept any log
     * event whose priority value is less than or equal to $priority.
     *
     * @param  int|array|Traversable $priority Priority
     * @param  string $operator Comparison operator
     * @return Priority
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($priority, $operator = null)
    {
        if ($priority instanceof Traversable) {
            $priority = iterator_to_array($priority);
        }
        if (is_array($priority)) {
            $operator = isset($priority['operator']) ? $priority['operator'] : null;
            $priority = isset($priority['priority']) ? $priority['priority'] : null;
        }
        if (!is_int($priority) && !ctype_digit($priority)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Priority must be a number, received "%s"',
                gettype($priority)
            ));
        }

        $this->priority = (int) $priority;
        $this->operator = $operator === null ? '<=' : $operator;
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param array $event event data
     * @return bool accepted?
     */
    public function filter(array $event)
    {
        return version_compare($event['priority'], $this->priority, $this->operator);
    }
}
