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
 * @package    Zend_InfoCard
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Interface.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * The interface required by all Zend_InfoCard Adapter classes to implement. It represents
 * a series of callback methods used by the component during processing of an information card
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_InfoCard_Adapter_Interface
{
    /**
     * Store the assertion's claims in persistent storage
     *
     * @param string $assertionURI The assertion type URI
     * @param string $assertionID The specific assertion ID
     * @param array $conditions An array of claims to store associated with the assertion
     * @return bool True on success, false on failure
     */
    public function storeAssertion($assertionURI, $assertionID, $conditions);

    /**
     * Retrieve the claims of a given assertion from persistent storage
     *
     * @param string $assertionURI The assertion type URI
     * @param string $assertionID The assertion ID to retrieve
     * @return mixed False if the assertion ID was not found for that URI, or an array of
     *               conditions associated with that assertion if found in the same format
     *                  provided
     */
    public function retrieveAssertion($assertionURI, $assertionID);

    /**
     * Remove the claims of a given assertion from persistent storage
     *
     * @param string $asserionURI The assertion type URI
     * @param string $assertionID The assertion ID to remove
     * @return bool True on success, false on failure
     */
    public function removeAssertion($asserionURI, $assertionID);
}
