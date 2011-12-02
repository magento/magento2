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
 * @version    $Id: Validate.php 20166 2010-01-09 19:00:17Z bkarwin $
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
class Zend_Service_DeveloperGarden_Request_SmsValidation_Validate
    extends Zend_Service_DeveloperGarden_Request_RequestAbstract
{
    /**
     * the keyword to be used for validation
     *
     * @var string
     */
    public $keyword = null;

    /**
     * the number
     *
     * @var string
     */
    public $number = null;

    /**
     * returns the keyword
     *
     * @return string $keyword
     */
    public function getKeyword ()
    {
        return $this->keyword;
    }

    /**
     * create the class for validation a sms keyword
     *
     * @param integer $environment
     * @param string $keyword
     * @param string $number
     */
    public function __construct($environment, $keyword = null, $number = null)
    {
        parent::__construct($environment);
        $this->setKeyword($keyword)
             ->setNumber($number);
    }

    /**
     * set a new keyword
     *
     * @param string $keyword
     * @return Zend_Service_DeveloperGarden_Request_SmsValidation_Validate
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
        return $this;
    }

    /**
     * returns the number
     *
     * @return string $number
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * set a new number
     *
     * @param string $number
     * @return Zend_Service_DeveloperGarden_Request_SmsValidation_Validate
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }
}
