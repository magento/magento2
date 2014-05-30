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
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: GetConferenceListRequest.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Request_RequestAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Request/RequestAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceListRequest
    extends Zend_Service_DeveloperGarden_Request_RequestAbstract
{
    /**
     * @var integer
     */
    public $what = null;

    /**
     * possible what values
     *
     * @var array
     */
    private $_whatValues = array(
        0 => 'all conferences',
        1 => 'just ad-hoc conferences',
        2 => 'just planned conferences',
        3 => 'just failed conferences',
    );

    /**
     * unique owner id
     *
     * @var string
     */
    public $ownerId = null;

    /**
     * constructor
     *
     * @param integer $environment
     * @param integer $what
     * @param string $ownerId
     */
    public function __construct($environment, $what = 0, $ownerId = null)
    {
        parent::__construct($environment);
        $this->setWhat($what)
             ->setOwnerId($ownerId);
    }

    /**
     * sets $what
     *
     * @param integer $what
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceListRequest
     */
    public function setWhat($what)
    {
        if (!array_key_exists($what, $this->_whatValues)) {
            #require_once 'Zend/Service/DeveloperGarden/Request/Exception.php';
            throw new Zend_Service_DeveloperGarden_Request_Exception('What value not allowed.');
        }
        $this->what = $what;
        return $this;
    }

    /**
     * sets $ownerId
     *
     * @param $ownerId
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceListRequest
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
        return $this;
    }
}
