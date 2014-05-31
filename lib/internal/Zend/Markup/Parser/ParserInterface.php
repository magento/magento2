<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Markup
 * @subpackage Parser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ParserInterface.php 20277 2010-01-14 14:17:12Z kokx $
 */

/**
 * @category   Zend
 * @package    Zend_Markup
 * @subpackage Parser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Markup_Parser_ParserInterface
{
    /**
     * Parse a string
     *
     * This should output something like this:
     *
     * <code>
     * array(
     *     array(
     *         'tag'        => '[tag="a" attr=val]',
     *         'type'       => Zend_Markup::TYPE_TAG,
     *         'name'       => 'tag',
     *         'stoppers'   => array('[/]', '[/tag]'),
     *         'attributes' => array(
     *             'tag'  => 'a',
     *             'attr' => 'val'
     *         )
     *     ),
     *     array(
     *         'tag'   => 'value',
     *         'type'  => Zend_Markup::TYPE_NONE
     *     ),
     *     array(
     *         'tag'        => '[/tag]',
     *         'type'       => Zend_Markup::TYPE_STOPPER,
     *         'name'       => 'tag',
     *         'stoppers'   => array(),
     *         'attributes' => array()
     *     )
     * )
     * </code>
     *
     * @param  string $value
     * @return array
     */
    public function parse($value);
}
