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
 * @package    Zend_EventManager
 * @subpackage UnitTest
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/EventManager/SharedEventCollection.php';

/**
 * Interface to automate setter injection for a SharedEventCollection instance
 *
 * @category   Zend
 * @package    Zend_EventManager
 * @subpackage UnitTest
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_EventManager_SharedEventCollectionAware
{
    /**
     * Inject an EventManager instance
     *
     * @param  Zend_EventManager_SharedEventCollection $sharedEventCollection
     * @return Zend_EventManager_SharedEventCollectionAware
     */
    public function setSharedCollections(Zend_EventManager_SharedEventCollection $sharedEventCollection);
}
