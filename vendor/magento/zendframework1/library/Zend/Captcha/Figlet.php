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

/** @see Zend_Text_Figlet */
#require_once 'Zend/Text/Figlet.php';

/**
 * Captcha based on figlet text rendering service
 *
 * Note that this engine seems not to like numbers
 *
 * @category   Zend
 * @package    Zend_Captcha
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Captcha_Figlet extends Zend_Captcha_Word
{
    /**
     * Figlet text renderer
     *
     * @var Zend_Text_Figlet
     */
    protected $_figlet;

    /**
     * Constructor
     *
     * @param null|string|array|Zend_Config $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->_figlet = new Zend_Text_Figlet($options);
    }

    /**
     * Generate new captcha
     *
     * @return string
     */
    public function generate()
    {
        $this->_useNumbers = false;
        return parent::generate();
    }

    /**
     * Display the captcha
     *
     * @param Zend_View_Interface $view
     * @param mixed $element
     * @return string
     */
    public function render(Zend_View_Interface $view = null, $element = null)
    {
        return '<pre>'
             . $this->_figlet->render($this->getWord())
             . "</pre>\n";
    }
}
