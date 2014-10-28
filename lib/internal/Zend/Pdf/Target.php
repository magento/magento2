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
 * @subpackage Actions
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Target.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * PDF target (action or destination)
 *
 * @package    Zend_Pdf
 * @subpackage Actions
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Target
{
    /**
     * Parse resource and return it as an Action or Explicit Destination
     *
     * $param Zend_Pdf_Element $resource
     * @return Zend_Pdf_Destination|
     * @throws Zend_Pdf_Exception
     */
    public static function load(Zend_Pdf_Element $resource) {
        #require_once 'Zend/Pdf/Element.php';
        if ($resource->getType() == Zend_Pdf_Element::TYPE_DICTIONARY) {
            if (($resource->Type === null  ||  $resource->Type->value =='Action')  &&  $resource->S !== null) {
                // It's a well-formed action, load it
                #require_once 'Zend/Pdf/Action.php';
                return Zend_Pdf_Action::load($resource);
            } else if ($resource->D !== null) {
                // It's a destination
                $resource = $resource->D;
            } else {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Wrong resource type.');
            }
        }

        if ($resource->getType() == Zend_Pdf_Element::TYPE_ARRAY  ||
            $resource->getType() == Zend_Pdf_Element::TYPE_NAME   ||
            $resource->getType() == Zend_Pdf_Element::TYPE_STRING) {
            // Resource is an array, just treat it as an explicit destination array
            #require_once 'Zend/Pdf/Destination.php';
            return Zend_Pdf_Destination::load($resource);
        } else {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception( 'Wrong resource type.' );
        }
    }

    /**
     * Get resource
     *
     * @internal
     * @return Zend_Pdf_Element
     */
    abstract public function getResource();
}
