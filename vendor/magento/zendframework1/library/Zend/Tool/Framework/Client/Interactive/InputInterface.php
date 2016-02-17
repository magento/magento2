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
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Tool_Framework_Client_Interactive_InputInterface
{

    /**
     * Handle Interactive Input Request
     *
     * @param Zend_Tool_Framework_Client_Interactive_InputRequest $inputRequest
     * @return Zend_Tool_Framework_Client_Interactive_InputResponse|string
     */
    public function handleInteractiveInputRequest(Zend_Tool_Framework_Client_Interactive_InputRequest $inputRequest);

    public function getMissingParameterPromptString(Zend_Tool_Framework_Provider_Interface $provider, Zend_Tool_Framework_Action_Interface $actionInterface, $missingParameterName);

}
