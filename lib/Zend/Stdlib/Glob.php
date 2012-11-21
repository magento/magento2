<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Stdlib
 */

namespace Zend\Stdlib;

/**
 * Wrapper for glob with fallback if GLOB_BRACE is not available.
 *
 * @category   Zend
 * @package    Zend_Stdlib
 */
abstract class Glob
{
    /**#@+
     * Glob constants.
     */
    const GLOB_MARK     = 0x01;
    const GLOB_NOSORT   = 0x02;
    const GLOB_NOCHECK  = 0x04;
    const GLOB_NOESCAPE = 0x08;
    const GLOB_BRACE    = 0x10;
    const GLOB_ONLYDIR  = 0x20;
    const GLOB_ERR      = 0x40;
    /**#@-*/

    /**
     * Find pathnames matching a pattern.
     *
     * @see    http://docs.php.net/glob
     * @param  string  $pattern
     * @param  integer $flags
     * @param  boolean $forceFallback
     * @return array|false
     */
    public static function glob($pattern, $flags, $forceFallback = false)
    {
        if (!defined('GLOB_BRACE') || $forceFallback) {
            return self::fallbackGlob($pattern, $flags);
        } else {
            return self::systemGlob($pattern, $flags);
        }
    }

    /**
     * Use the glob function provided by the system.
     *
     * @param  string  $pattern
     * @param  integer $flags
     * @return array|false
     */
    protected static function systemGlob($pattern, $flags)
    {
        if ($flags) {
            $flagMap = array(
                self::GLOB_MARK     => GLOB_MARK,
                self::GLOB_NOSORT   => GLOB_NOSORT,
                self::GLOB_NOCHECK  => GLOB_NOCHECK,
                self::GLOB_NOESCAPE => GLOB_NOESCAPE,
                self::GLOB_BRACE    => GLOB_BRACE,
                self::GLOB_ONLYDIR  => GLOB_ONLYDIR,
                self::GLOB_ERR      => GLOB_ERR,
            );

            $globFlags = 0;

            foreach ($flagMap as $internalFlag => $globFlag) {
                if ($flags & $internalFlag) {
                    $globFlags |= $globFlag;
                }
            }
        } else {
            $globFlags = 0;
        }

        return glob($pattern, $globFlags);
    }

    /**
     * Expand braces manually, then use the system glob.
     *
     * @param  string  $pattern
     * @param  integer $flags
     * @return array|false
     */
    protected static function fallbackGlob($pattern, $flags)
    {
        if (!$flags & self::GLOB_BRACE) {
            return self::systemGlob($pattern, $flags);
        }

        $flags &= ~self::GLOB_BRACE;
        $length = strlen($pattern);
        $paths  = array();

        if ($flags & self::GLOB_NOESCAPE) {
            $begin = strpos($pattern, '{');
        } else {
            $begin = 0;

            while (true) {
                if ($begin === $length) {
                    $begin = false;
                    break;
                } elseif ($pattern[$begin] === '\\' && ($begin + 1) < $length) {
                    $begin++;
                } elseif ($pattern[$begin] === '{') {
                    break;
                }

                $begin++;
            }
        }

        if ($begin === false) {
            return self::systemGlob($pattern, $flags);
        }

        $next = self::nextBraceSub($pattern, $begin + 1, $flags);

        if ($next === null) {
            return self::systemGlob($pattern, $flags);
        }

        $rest = $next;

        while ($pattern[$rest] !== '}') {
            $rest = self::nextBraceSub($pattern, $rest + 1, $flags);

            if ($rest === null) {
                return self::systemGlob($pattern, $flags);
            }
        }

        $p = $begin + 1;

        while (true) {
            $subPattern = substr($pattern, 0, $begin)
                        . substr($pattern, $p, $next - $p)
                        . substr($pattern, $rest + 1);

            $result = self::fallbackGlob($subPattern, $flags | self::GLOB_BRACE);

            if ($result) {
                $paths = array_merge($paths, $result);
            }

            if ($pattern[$next] === '}') {
                break;
            }

            $p    = $next + 1;
            $next = self::nextBraceSub($pattern, $p, $flags);
        }

        return array_unique($paths);
    }

    /**
     * Find the end of the sub-pattern in a brace expression.
     *
     * @param  string  $pattern
     * @param  integer $begin
     * @param  integer $flags
     * @return integer|null
     */
    protected static function nextBraceSub($pattern, $begin, $flags)
    {
        $length  = strlen($pattern);
        $depth   = 0;
        $current = $begin;

        while ($current < $length) {
            if (!$flags & self::GLOB_NOESCAPE && $pattern[$current] === '\\') {
                if (++$current === $length) {
                    break;
                }

                $current++;
            } else {
                if (($pattern[$current] === '}' && $depth-- === 0) || ($pattern[$current] === ',' && $depth === 0)) {
                    break;
                } elseif ($pattern[$current++] === '{') {
                    $depth++;
                }
            }
        }

        return ($current < $length ? $current : null);
    }
}
