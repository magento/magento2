<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Text;

use Zend\Stdlib\StringUtils;

/**
 * Contains multibyte safe string methods
 */
class MultiByte
{
    /**
     * Word wrap
     *
     * @param  string  $string
     * @param  int $width
     * @param  string  $break
     * @param  bool $cut
     * @param  string  $charset
     * @throws Exception\InvalidArgumentException
     * @return string
     * @deprecated Please use Zend\Stdlib\StringUtils instead
     */
    public static function wordWrap($string, $width = 75, $break = "\n", $cut = false, $charset = 'utf-8')
    {
        trigger_error(sprintf(
            "This method is deprecated, please use '%s' instead",
            'Zend\Stdlib\StringUtils::getWrapper(<charset>)->wordWrap'
        ), E_USER_DEPRECATED);

        try {
            return StringUtils::getWrapper($charset)->wordWrap($string, $width, $break, $cut);
        } catch (\Zend\Stdlib\Exception\InvalidArgumentException $e) {
            throw new Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * String padding
     *
     * @param  string  $input
     * @param  int $padLength
     * @param  string  $padString
     * @param  int $padType
     * @param  string  $charset
     * @return string
     * @deprecated Please use Zend\Stdlib\StringUtils instead
     */
    public static function strPad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT, $charset = 'utf-8')
    {
        trigger_error(sprintf(
            "This method is deprecated, please use '%s' instead",
            'Zend\Stdlib\StringUtils::getWrapper(<charset>)->strPad'
        ), E_USER_DEPRECATED);

        return StringUtils::getWrapper($charset)->strPad($input, $padLength, $padString, $padType);
    }
}
