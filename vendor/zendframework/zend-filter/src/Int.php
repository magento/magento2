<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

/**
 * Stub class for backwards compatibility.
 *
 * Since PHP 7 adds "int" as a reserved keyword, we can no longer have a class
 * named that and retain PHP 7 compatibility. The original class has been
 * renamed to "ToInt", and this class is now an extension of it. It raises an
 * E_USER_DEPRECATED to warn users to migrate.
 *
 * @deprecated
 */
class Int extends ToInt
{
    public function __construct()
    {
        trigger_error(
            sprintf(
                'The class %s has been deprecated; please use %s\\ToInt',
                __CLASS__,
                __NAMESPACE__
            ),
            E_USER_DEPRECATED
        );
    }
}
