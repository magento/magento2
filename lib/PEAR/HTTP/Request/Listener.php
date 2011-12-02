<?php
/**
 * Listener for HTTP_Request and HTTP_Response objects
 *
 * PHP versions 4 and 5
 * 
 * LICENSE:
 *
 * Copyright (c) 2002-2007, Richard Heyes
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * o Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * o Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * o The names of the authors may not be used to endorse or promote
 *   products derived from this software without specific prior written
 *   permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    HTTP
 * @package     HTTP_Request
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2002-2007 Richard Heyes
 * @license     http://opensource.org/licenses/bsd-license.php New BSD License
 * @version     CVS: $Id: Listener.php,v 1.3 2007/05/18 10:33:31 avb Exp $
 * @link        http://pear.php.net/package/HTTP_Request/ 
 */

/**
 * Listener for HTTP_Request and HTTP_Response objects
 *
 * This class implements the Observer part of a Subject-Observer
 * design pattern.
 *
 * @category    HTTP
 * @package     HTTP_Request
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 1.4.4
 */
class HTTP_Request_Listener 
{
   /**
    * A listener's identifier
    * @var string
    */
    var $_id;

   /**
    * Constructor, sets the object's identifier
    *
    * @access public
    */
    function HTTP_Request_Listener()
    {
        $this->_id = md5(uniqid('http_request_', 1));
    }


   /**
    * Returns the listener's identifier
    *
    * @access public
    * @return string
    */
    function getId()
    {
        return $this->_id;
    }


   /**
    * This method is called when Listener is notified of an event
    *
    * @access   public
    * @param    object  an object the listener is attached to
    * @param    string  Event name
    * @param    mixed   Additional data
    * @abstract
    */
    function update(&$subject, $event, $data = null)
    {
        echo "Notified of event: '$event'\n";
        if (null !== $data) {
            echo "Additional data: ";
            var_dump($data);
        }
    }
}
?>
