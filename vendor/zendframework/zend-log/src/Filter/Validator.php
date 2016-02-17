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
use Zend\Validator\ValidatorInterface as ZendValidator;

class Validator implements FilterInterface
{
    /**
     * Regex to match
     *
     * @var ZendValidator
     */
    protected $validator;

    /**
     * Filter out any log messages not matching the validator
     *
     * @param  ZendValidator|array|Traversable $validator
     * @throws Exception\InvalidArgumentException
     * @return Validator
     */
    public function __construct($validator)
    {
        if ($validator instanceof Traversable) {
            $validator = iterator_to_array($validator);
        }
        if (is_array($validator)) {
            $validator = isset($validator['validator']) ? $validator['validator'] : null;
        }
        if (!$validator instanceof ZendValidator) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Parameter of type %s is invalid; must implement Zend\Validator\ValidatorInterface',
                (is_object($validator) ? get_class($validator) : gettype($validator))
            ));
        }
        $this->validator = $validator;
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param array $event event data
     * @return bool
     */
    public function filter(array $event)
    {
        return $this->validator->isValid($event['message']);
    }
}
