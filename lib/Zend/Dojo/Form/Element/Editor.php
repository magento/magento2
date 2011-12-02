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
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Editor.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Dojo_Form_Element_Dijit */
#require_once 'Zend/Dojo/Form/Element/Dijit.php';

/**
 * Editor dijit
 *
 * @uses       Zend_Dojo_Form_Element_Dijit
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Dojo_Form_Element_Editor extends Zend_Dojo_Form_Element_Dijit
{
    /**
     * @var string View helper
     */
    public $helper = 'Editor';

    /**
     * Add a single event to connect to the editing area
     *
     * @param  string $event
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function addCaptureEvent($event)
    {
        $event = (string) $event;
        $captureEvents = $this->getCaptureEvents();
        if (in_array($event, $captureEvents)) {
            return $this;
        }

        $captureEvents[] = (string) $event;
        $this->setDijitParam('captureEvents', $captureEvents);
        return $this;
    }

    /**
     * Add multiple capture events
     *
     * @param  array $events
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function addCaptureEvents(array $events)
    {
        foreach ($events as $event) {
            $this->addCaptureEvent($event);
        }
        return $this;
    }

    /**
     * Overwrite many capture events at once
     *
     * @param  array $events
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function setCaptureEvents(array $events)
    {
        $this->clearCaptureEvents();
        $this->addCaptureEvents($events);
        return $this;
    }

    /**
     * Get all capture events
     *
     * @return array
     */
    public function getCaptureEvents()
    {
        if (!$this->hasDijitParam('captureEvents')) {
            return array();
        }
        return $this->getDijitParam('captureEvents');
    }

    /**
     * Is a given capture event registered?
     *
     * @param  string $event
     * @return bool
     */
    public function hasCaptureEvent($event)
    {
        $captureEvents = $this->getCaptureEvents();
        return in_array((string) $event, $captureEvents);
    }

    /**
     * Remove a given capture event
     *
     * @param  string $event
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function removeCaptureEvent($event)
    {
        $event = (string) $event;
        $captureEvents = $this->getCaptureEvents();
        if (false === ($index = array_search($event, $captureEvents))) {
            return $this;
        }
        unset($captureEvents[$index]);
        $this->setDijitParam('captureEvents', $captureEvents);
        return $this;
    }

    /**
     * Clear all capture events
     *
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function clearCaptureEvents()
    {
        return $this->removeDijitParam('captureEvents');
    }

    /**
     * Add a single event to the dijit
     *
     * @param  string $event
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function addEvent($event)
    {
        $event = (string) $event;
        $events = $this->getEvents();
        if (in_array($event, $events)) {
            return $this;
        }

        $events[] = (string) $event;
        $this->setDijitParam('events', $events);
        return $this;
    }

    /**
     * Add multiple events
     *
     * @param  array $events
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function addEvents(array $events)
    {
        foreach ($events as $event) {
            $this->addEvent($event);
        }
        return $this;
    }

    /**
     * Overwrite many events at once
     *
     * @param  array $events
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function setEvents(array $events)
    {
        $this->clearEvents();
        $this->addEvents($events);
        return $this;
    }

    /**
     * Get all events
     *
     * @return array
     */
    public function getEvents()
    {
        if (!$this->hasDijitParam('events')) {
            return array();
        }
        return $this->getDijitParam('events');
    }

    /**
     * Is a given event registered?
     *
     * @param  string $event
     * @return bool
     */
    public function hasEvent($event)
    {
        $events = $this->getEvents();
        return in_array((string) $event, $events);
    }

    /**
     * Remove a given event
     *
     * @param  string $event
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function removeEvent($event)
    {
        $events = $this->getEvents();
        if (false === ($index = array_search($event, $events))) {
            return $this;
        }
        unset($events[$index]);
        $this->setDijitParam('events', $events);
        return $this;
    }

    /**
     * Clear all events
     *
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function clearEvents()
    {
        return $this->removeDijitParam('events');
    }

    /**
     * Add a single editor plugin
     *
     * @param  string $plugin
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function addPlugin($plugin)
    {
        $plugin = (string) $plugin;
        $plugins = $this->getPlugins();
        if (in_array($plugin, $plugins)) {
            return $this;
        }

        $plugins[] = (string) $plugin;
        $this->setDijitParam('plugins', $plugins);
        return $this;
    }

    /**
     * Add multiple plugins
     *
     * @param  array $plugins
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function addPlugins(array $plugins)
    {
        foreach ($plugins as $plugin) {
            $this->addPlugin($plugin);
        }
        return $this;
    }

    /**
     * Overwrite many plugins at once
     *
     * @param  array $plugins
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function setPlugins(array $plugins)
    {
        $this->clearPlugins();
        $this->addPlugins($plugins);
        return $this;
    }

    /**
     * Get all plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        if (!$this->hasDijitParam('plugins')) {
            return array();
        }
        return $this->getDijitParam('plugins');
    }

    /**
     * Is a given plugin registered?
     *
     * @param  string $plugin
     * @return bool
     */
    public function hasPlugin($plugin)
    {
        $plugins = $this->getPlugins();
        return in_array((string) $plugin, $plugins);
    }

    /**
     * Remove a given plugin
     *
     * @param  string $plugin
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function removePlugin($plugin)
    {
        $plugins = $this->getPlugins();
        if (false === ($index = array_search($plugin, $plugins))) {
            return $this;
        }
        unset($plugins[$index]);
        $this->setDijitParam('plugins', $plugins);
        return $this;
    }

    /**
     * Clear all plugins
     *
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function clearPlugins()
    {
        return $this->removeDijitParam('plugins');
    }

    /**
     * Set edit action interval
     *
     * @param  int $interval
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function setEditActionInterval($interval)
    {
        return $this->setDijitParam('editActionInterval', (int) $interval);
    }

    /**
     * Get edit action interval; defaults to 3
     *
     * @return int
     */
    public function getEditActionInterval()
    {
        if (!$this->hasDijitParam('editActionInterval')) {
            $this->setEditActionInterval(3);
        }
        return $this->getDijitParam('editActionInterval');
    }

    /**
     * Set focus on load flag
     *
     * @param  bool $flag
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function setFocusOnLoad($flag)
    {
        return $this->setDijitParam('focusOnLoad', (bool) $flag);
    }

    /**
     * Retrieve focus on load flag
     *
     * @return bool
     */
    public function getFocusOnLoad()
    {
        if (!$this->hasDijitParam('focusOnLoad')) {
             return false;
        }
        return $this->getDijitParam('focusOnLoad');
    }

    /**
     * Set editor height
     *
     * @param  string|int $height
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function setHeight($height)
    {
        if (!preg_match('/^\d+(em|px|%)?$/i', $height)) {
            #require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception('Invalid height provided; must be integer or CSS measurement');
        }
        if (!preg_match('/(em|px|%)$/', $height)) {
            $height .= 'px';
        }
        return $this->setDijitParam('height', $height);
    }

    /**
     * Retrieve height
     *
     * @return string
     */
    public function getHeight()
    {
        if (!$this->hasDijitParam('height')) {
            return '300px';
        }
        return $this->getDijitParam('height');
    }

    /**
     * Set whether or not to inherit parent's width
     *
     * @param  bool $flag
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function setInheritWidth($flag)
    {
        return $this->setDijitParam('inheritWidth', (bool) $flag);
    }

    /**
     * Whether or not to inherit the parent's width
     *
     * @return bool
     */
    public function getInheritWidth()
    {
        if (!$this->hasDijitParam('inheritWidth')) {
            return false;
        }
        return $this->getDijitParam('inheritWidth');
    }

    /**
     * Set minimum height of editor
     *
     * @param  string|int $minHeight
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function setMinHeight($minHeight)
    {
        if (!preg_match('/^\d+(em)?$/i', $minHeight)) {
            #require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception('Invalid minHeight provided; must be integer or CSS measurement');
        }
        if ('em' != substr($minHeight, -2)) {
            $minHeight .= 'em';
        }
        return $this->setDijitParam('minHeight', $minHeight);
    }

    /**
     * Get minimum height of editor
     *
     * @return string
     */
    public function getMinHeight()
    {
        if (!$this->hasDijitParam('minHeight')) {
            return '1em';
        }
        return $this->getDijitParam('minHeight');
    }

    /**
     * Add a custom stylesheet
     *
     * @param  string $styleSheet
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function addStyleSheet($styleSheet)
    {
        $stylesheets = $this->getStyleSheets();
        if (strstr($stylesheets, ';')) {
            $stylesheets = explode(';', $stylesheets);
        } elseif (!empty($stylesheets)) {
            $stylesheets = (array) $stylesheets;
        } else {
            $stylesheets = array();
        }
        if (!in_array($styleSheet, $stylesheets)) {
            $stylesheets[] = (string) $styleSheet;
        }
        return $this->setDijitParam('styleSheets', implode(';', $stylesheets));
    }

    /**
     * Add multiple custom stylesheets
     *
     * @param  array $styleSheets
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function addStyleSheets(array $styleSheets)
    {
        foreach ($styleSheets as $styleSheet) {
            $this->addStyleSheet($styleSheet);
        }
        return $this;
    }

    /**
     * Overwrite all stylesheets
     *
     * @param  array $styleSheets
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function setStyleSheets(array $styleSheets)
    {
        $this->clearStyleSheets();
        return $this->addStyleSheets($styleSheets);
    }

    /**
     * Get all stylesheets
     *
     * @return string
     */
    public function getStyleSheets()
    {
        if (!$this->hasDijitParam('styleSheets')) {
            return '';
        }
        return $this->getDijitParam('styleSheets');
    }

    /**
     * Is a given stylesheet registered?
     *
     * @param  string $styleSheet
     * @return bool
     */
    public function hasStyleSheet($styleSheet)
    {
        $styleSheets = $this->getStyleSheets();
        $styleSheets = explode(';', $styleSheets);
        return in_array($styleSheet, $styleSheets);
    }

    /**
     * Remove a single stylesheet
     *
     * @param  string $styleSheet
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function removeStyleSheet($styleSheet)
    {
        $styleSheets = $this->getStyleSheets();
        $styleSheets = explode(';', $styleSheets);
        if (false !== ($index = array_search($styleSheet, $styleSheets))) {
            unset($styleSheets[$index]);
            $this->setDijitParam('styleSheets', implode(';', $styleSheets));
        }
        return $this;
    }

    /**
     * Clear all stylesheets
     *
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function clearStyleSheets()
    {
        if ($this->hasDijitParam('styleSheets')) {
            $this->removeDijitParam('styleSheets');
        }
        return $this;
    }

    /**
     * Set update interval
     *
     * @param  int $interval
     * @return Zend_Dojo_Form_Element_Editor
     */
    public function setUpdateInterval($interval)
    {
        return $this->setDijitParam('updateInterval', (int) $interval);
    }

    /**
     * Get update interval
     *
     * @return int
     */
    public function getUpdateInterval()
    {
        if (!$this->hasDijitParam('updateInterval')) {
             return 200;
        }
        return $this->getDijitParam('updateInterval');
    }
}
