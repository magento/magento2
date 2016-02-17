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
 * @package    Zend_Captcha
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** @see Zend_Captcha_Word */
#require_once 'Zend/Captcha/Word.php';

/**
 * Example dumb word-based captcha
 *
 * Note that only rendering is necessary for word-based captcha
 *
 * @category   Zend
 * @package    Zend_Captcha
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
*/
class Zend_Captcha_Dumb extends Zend_Captcha_Word
{
    /**
     * CAPTCHA label
     * @type string
     */
    protected $_label = 'Please type this word backwards';

    /**
     * Set the label for the CAPTCHA
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->_label = $label;
    }

    /**
     * Retrieve the label for the CAPTCHA
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }
    /**
     * Render the captcha
     *
     * @param  Zend_View_Interface $view
     * @param  mixed $element
     * @return string
     */
    public function render(Zend_View_Interface $view = null, $element = null)
    {
        return $this->getLabel() . ': <b>'
             . strrev($this->getWord())
             . '</b>';
    }
}
