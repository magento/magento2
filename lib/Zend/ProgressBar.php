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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ProgressBar.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_ProgressBar offers an interface for multiple enviroments.
 *
 * @category  Zend
 * @package   Zend_ProgressBar
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_ProgressBar
{
    /**
     * Min value
     *
     * @var float
     */
    protected $_min;

    /**
     * Max value
     *
     * @var float
     */
    protected $_max;

    /**
     * Current value
     *
     * @var float
     */
    protected $_current;

    /**
     * Start time of the progressbar, required for ETA
     *
     * @var integer
     */
    protected $_startTime;

    /**
     * Current status text
     *
     * @var string
     */
    protected $_statusText = null;

    /**
     * Adapter for the output
     *
     * @var Zend_ProgressBar_Adapter
     */
    protected $_adapter;

    /**
     * Namespace for keeping the progressbar persistent
     *
     * @var string
     */
    protected $_persistenceNamespace = null;

    /**
     * Create a new progressbar backend.
     *
     * @param  Zend_ProgressBar_Adapter $adapter
     * @param  float                    $min
     * @param  float                    $max
     * @param  string                   $persistenceNamespace
     * @throws Zend_ProgressBar_Exception When $min is greater than $max
     */
    public function __construct(Zend_ProgressBar_Adapter $adapter, $min = 0, $max = 100, $persistenceNamespace = null)
    {
        // Check min/max values and set them
        if ($min > $max) {
            #require_once 'Zend/ProgressBar/Exception.php';
            throw new Zend_ProgressBar_Exception('$max must be greater than $min');
        }

        $this->_min     = (float) $min;
        $this->_max     = (float) $max;
        $this->_current = (float) $min;

        // See if we have to open a session namespace
        if ($persistenceNamespace !== null) {
            #require_once 'Zend/Session/Namespace.php';

            $this->_persistenceNamespace = new Zend_Session_Namespace($persistenceNamespace);
        }

        // Set adapter
        $this->_adapter = $adapter;

        // Track the start time
        $this->_startTime = time();

        // See If a persistenceNamespace exists and handle accordingly
        if ($this->_persistenceNamespace !== null) {
            if (isset($this->_persistenceNamespace->isSet)) {
                $this->_startTime  = $this->_persistenceNamespace->startTime;
                $this->_current    = $this->_persistenceNamespace->current;
                $this->_statusText = $this->_persistenceNamespace->statusText;
            } else {
                $this->_persistenceNamespace->isSet      = true;
                $this->_persistenceNamespace->startTime  = $this->_startTime;
                $this->_persistenceNamespace->current    = $this->_current;
                $this->_persistenceNamespace->statusText = $this->_statusText;
            }
        } else {
            $this->update();
        }
    }

    /**
     * Get the current adapter
     *
     * @return Zend_ProgressBar_Adapter
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Update the progressbar
     *
     * @param  float  $value
     * @param  string $text
     * @return void
     */
    public function update($value = null, $text = null)
    {
        // Update value if given
        if ($value !== null) {
            $this->_current = min($this->_max, max($this->_min, $value));
        }

        // Update text if given
        if ($text !== null) {
            $this->_statusText = $text;
        }

        // See if we have to update a namespace
        if ($this->_persistenceNamespace !== null) {
            $this->_persistenceNamespace->current    = $this->_current;
            $this->_persistenceNamespace->statusText = $this->_statusText;
        }

        // Calculate percent
        if ($this->_min === $this->_max) {
            $percent = false;
        } else {
            $percent = (float) ($this->_current - $this->_min) / ($this->_max - $this->_min);
        }

        // Calculate ETA
        $timeTaken = time() - $this->_startTime;

        if ($percent === .0 || $percent === false) {
            $timeRemaining = null;
        } else {
            $timeRemaining = round(((1 / $percent) * $timeTaken) - $timeTaken);
        }

        // Poll the adapter
        $this->_adapter->notify($this->_current, $this->_max, $percent, $timeTaken, $timeRemaining, $this->_statusText);
    }

    /**
     * Update the progressbar to the next value
     *
     * @param  string $text
     * @return void
     */
    public function next($diff = 1, $text = null)
    {
        $this->update(max($this->_min, min($this->_max, $this->_current + $diff)), $text);
    }

    /**
     * Call the adapters finish() behaviour
     *
     * @return void
     */
    public function finish()
    {
        if ($this->_persistenceNamespace !== null) {
            unset($this->_persistenceNamespace->isSet);
        }

        $this->_adapter->finish();
    }
}
