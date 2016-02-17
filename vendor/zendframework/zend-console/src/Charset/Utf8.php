<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Console\Charset;

/**
 * UTF-8 box drawing
 *
 * @link http://en.wikipedia.org/wiki/Box-drawing_characters
 */
class Utf8 implements CharsetInterface
{
    const ACTIVATE          = "";
    const DEACTIVATE        = "";

    const BLOCK = "█";
    const SHADE_LIGHT = "░";
    const SHADE_MEDIUM = "▒";
    const SHADE_DARK = "▓";

    const LINE_SINGLE_EW = "─";
    const LINE_SINGLE_NS = "│";
    const LINE_SINGLE_NW = "┌";
    const LINE_SINGLE_NE = "┐";
    const LINE_SINGLE_SE = "┘";
    const LINE_SINGLE_SW = "└";
    const LINE_SINGLE_CROSS = "┼";

    const LINE_DOUBLE_EW = "═";
    const LINE_DOUBLE_NS = "║";
    const LINE_DOUBLE_NW = "╔";
    const LINE_DOUBLE_NE = "╗";
    const LINE_DOUBLE_SE = "╝";
    const LINE_DOUBLE_SW = "╚";
    const LINE_DOUBLE_CROSS = "╬";

    const LINE_BLOCK_EW = "█";
    const LINE_BLOCK_NS = "█";
    const LINE_BLOCK_NW = "█";
    const LINE_BLOCK_NE = "█";
    const LINE_BLOCK_SE = "█";
    const LINE_BLOCK_SW = "█";
    const LINE_BLOCK_CROSS = "█";
}
