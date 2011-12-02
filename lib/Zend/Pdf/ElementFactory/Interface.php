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
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Interface.php 22797 2010-08-06 15:02:12Z alexander $
 */

/**
 * PDF element factory interface.
 * Responsibility is to log PDF changes
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Pdf_ElementFactory_Interface
{
    /**
     * Get factory
     *
     * @return Zend_Pdf_ElementFactory_Interface
     */
    public function getFactory();

    /**
     * Close factory and clean-up resources
     *
     * @internal
     */
    public function close();

    /**
     * Get source factory object
     *
     * @return Zend_Pdf_ElementFactory
     */
    public function resolve();

    /**
     * Get factory ID
     *
     * @return integer
     */
    public function getId();

    /**
     * Set object counter
     *
     * @param integer $objCount
     */
    public function setObjectCount($objCount);

    /**
     * Get object counter
     *
     * @return integer
     */
    public function getObjectCount();

    /**
     * Attach factory to the current;
     *
     * @param Zend_Pdf_ElementFactory_Interface $factory
     */
    public function attach(Zend_Pdf_ElementFactory_Interface $factory);

    /**
     * Calculate object enumeration shift.
     *
     * @param Zend_Pdf_ElementFactory_Interface $factory
     * @return integer
     */
    public function calculateShift(Zend_Pdf_ElementFactory_Interface $factory);

    /**
     * Clean enumeration shift cache.
     * Has to be used after PDF render operation to let followed updates be correct.
     *
     * @param Zend_Pdf_ElementFactory_Interface $factory
     * @return integer
     */
    public function cleanEnumerationShiftCache();

    /**
     * Retrive object enumeration shift.
     *
     * @param Zend_Pdf_ElementFactory_Interface $factory
     * @return integer
     * @throws Zend_Pdf_Exception
     */
    public function getEnumerationShift(Zend_Pdf_ElementFactory_Interface $factory);

    /**
     * Mark object as modified in context of current factory.
     *
     * @param Zend_Pdf_Element_Object $obj
     * @throws Zend_Pdf_Exception
     */
    public function markAsModified(Zend_Pdf_Element_Object $obj);

    /**
     * Remove object in context of current factory.
     *
     * @param Zend_Pdf_Element_Object $obj
     * @throws Zend_Pdf_Exception
     */
    public function remove(Zend_Pdf_Element_Object $obj);

    /**
     * Generate new Zend_Pdf_Element_Object
     *
     * @todo Reusage of the freed object. It's not a support of new feature, but only improvement.
     *
     * @param Zend_Pdf_Element $objectValue
     * @return Zend_Pdf_Element_Object
     */
    public function newObject(Zend_Pdf_Element $objectValue);

    /**
     * Generate new Zend_Pdf_Element_Object_Stream
     *
     * @todo Reusage of the freed object. It's not a support of new feature, but only improvement.
     *
     * @param mixed $objectValue
     * @return Zend_Pdf_Element_Object_Stream
     */
    public function newStreamObject($streamValue);

    /**
     * Enumerate modified objects.
     * Returns array of Zend_Pdf_UpdateInfoContainer
     *
     * @param Zend_Pdf_ElementFactory $rootFactory
     * @return array
     */
    public function listModifiedObjects($rootFactory = null);

    /**
     * Check if PDF file was modified
     *
     * @return boolean
     */
    public function isModified();
}
