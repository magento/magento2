<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Uri
 */

namespace Zend\Uri\Exception;

/**
 * @category   Zend
 * @package    Zend_Uri
 * @subpackage Exception
 */
class InvalidUriPartException extends InvalidArgumentException
{
    /**
     * Part-specific error codes
     *
     * @var integer
     */
    const INVALID_SCHEME    = 1;
    const INVALID_USER      = 2;
    const INVALID_PASSWORD  = 4;
    const INVALID_USERINFO  = 6;
    const INVALID_HOSTNAME  = 8;
    const INVALID_PORT      = 16;
    const INVALID_AUTHORITY = 30;
    const INVALID_PATH      = 32;
    const INVALID_QUERY     = 64;
    const INVALID_FRAGMENT  = 128;
}
