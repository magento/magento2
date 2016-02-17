<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

/**
 * @deprecated 2.4.8 Add Zend\Validator\NotEmpty validator to the ValidatorChain.
 */
interface EmptyContextInterface
{
    /**
     * @deprecated 2.4.8 Add Zend\Validator\NotEmpty validator to the ValidatorChain and set this to `true`.
     *
     * @param bool $continueIfEmpty
     * @return self
     */
    public function setContinueIfEmpty($continueIfEmpty);

    /**
     * @deprecated 2.4.8 Add Zend\Validator\NotEmpty validator to the ValidatorChain. Should always return `true`.
     *
     * @return bool
     */
    public function continueIfEmpty();
}
