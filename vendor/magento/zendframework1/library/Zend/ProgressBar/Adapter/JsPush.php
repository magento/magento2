<?php
/**
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
 * @package    Zend_ProgressBar
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Json
 */
#require_once 'Zend/Json.php';

/**
 * @see Zend_ProgressBar_Adapter
 */
#require_once 'Zend/ProgressBar/Adapter.php';

/**
 * Zend_ProgressBar_Adapter_JsPush offers a simple method for updating a
 * progressbar in a browser.
 *
 * @category  Zend
 * @package   Zend_ProgressBar
 * @uses      Zend_ProgressBar_Adapter_Interface
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_ProgressBar_Adapter_JsPush extends Zend_ProgressBar_Adapter
{
    /**
     * Name of the JavaScript method to call on update
     *
     * @var string
     */
    protected $_updateMethodName = 'Zend_ProgressBar_Update';

    /**
     * Name of the JavaScript method to call on finish
     *
     * @var string
     */
    protected $_finishMethodName;

    /**
     * Set the update method name
     *
     * @param  string $methodName
     * @return Zend_ProgressBar_Adapter_JsPush
     */
    public function setUpdateMethodName($methodName)
    {
        $this->_updateMethodName = $methodName;

        return $this;
    }

    /**
     * Set the finish method name
     *
     * @param  string $methodName
     * @return Zend_ProgressBar_Adapter_JsPush
     */
    public function setFinishMethodName($methodName)
    {
        $this->_finishMethodName = $methodName;

        return $this;
    }

    /**
     * Defined by Zend_ProgressBar_Adapter_Interface
     *
     * @param  float   $current       Current progress value
     * @param  float   $max           Max progress value
     * @param  float   $percent       Current percent value
     * @param  integer $timeTaken     Taken time in seconds
     * @param  integer $timeRemaining Remaining time in seconds
     * @param  string  $text          Status text
     * @return void
     */
    public function notify($current, $max, $percent, $timeTaken, $timeRemaining, $text)
    {
        $arguments = array(
            'current'       => $current,
            'max'           => $max,
            'percent'       => ($percent * 100),
            'timeTaken'     => $timeTaken,
            'timeRemaining' => $timeRemaining,
            'text'          => $text
        );

        $data = '<script type="text/javascript">'
              . 'parent.' . $this->_updateMethodName . '(' . Zend_Json::encode($arguments) . ');'
              . '</script>';

        // Output the data
        $this->_outputData($data);
    }

    /**
     * Defined by Zend_ProgressBar_Adapter_Interface
     *
     * @return void
     */
    public function finish()
    {
        if ($this->_finishMethodName === null) {
            return;
        }

        $data = '<script type="text/javascript">'
              . 'parent.' . $this->_finishMethodName . '();'
              . '</script>';

        $this->_outputData($data);
    }

    /**
     * Outputs given data the user agent.
     *
     * This split-off is required for unit-testing.
     *
     * @param  string $data
     * @return void
     */
    protected function _outputData($data)
    {
        // 1024 padding is required for Safari, while 256 padding is required
        // for Internet Explorer. The <br /> is required so Safari actually
        // executes the <script />
        echo str_pad($data . '<br />', 1024, ' ', STR_PAD_RIGHT) . "\n";

        flush();
        ob_flush();
    }
}
