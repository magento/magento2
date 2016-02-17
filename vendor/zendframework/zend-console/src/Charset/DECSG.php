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
 * DEC Special Graphics (VT100 line drawing) character set
 *
 * @link http://vt100.net/docs/vt220-rm/table2-4.html
 */
class DECSG implements CharsetInterface
{
    const ACTIVATE          = "\x1b(0";
    const DEACTIVATE        = "\x1b(B";

    const BLOCK             = "\x61";
    const SHADE_LIGHT       = "\x61";
    const SHADE_MEDIUM      = "\x61";
    const SHADE_DARK        = "\x61";

    const LINE_SINGLE_EW    = "\x71";
    const LINE_SINGLE_NS    = "\x78";
    const LINE_SINGLE_NW    = "\x6c";
    const LINE_SINGLE_NE    = "\x6b";
    const LINE_SINGLE_SE    = "\x6a";
    const LINE_SINGLE_SW    = "\x6d";
    const LINE_SINGLE_CROSS = "\x6e";

    const LINE_DOUBLE_EW    = "\x73";
    const LINE_DOUBLE_NS    = "\x78";
    const LINE_DOUBLE_NW    = "\x6c";
    const LINE_DOUBLE_NE    = "\x5b";
    const LINE_DOUBLE_SE    = "\x6a";
    const LINE_DOUBLE_SW    = "\x6d";
    const LINE_DOUBLE_CROSS = "\x6e";

    const LINE_BLOCK_EW    = "\x61";
    const LINE_BLOCK_NS    = "\x61";
    const LINE_BLOCK_NW    = "\x61";
    const LINE_BLOCK_NE    = "\x61";
    const LINE_BLOCK_SE    = "\x61";
    const LINE_BLOCK_SW    = "\x61";
    const LINE_BLOCK_CROSS = "\x61";
}
