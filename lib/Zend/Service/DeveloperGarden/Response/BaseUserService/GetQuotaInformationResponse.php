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
 * @version    $Id: GetQuotaInformationResponse.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Response_ResponseAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ResponseAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Response_BaseUserService_GetQuotaInformationResponse
    extends Zend_Service_DeveloperGarden_Response_ResponseAbstract
{
    /**
     * System defined limit of quota points per day
     *
     * @var integer
     */
    public $maxQuota = null;

    /**
     * User specific limit of quota points per day
     * cant be more than $maxQuota
     *
     * @var integer
     */
    public $maxUserQuota = null;

    /**
     * Used quota points for the current day
     *
     * @var integer
     */
    public $quotaLevel = null;

    /**
     * returns the quotaLevel
     *
     * @return integer
     */
    public function getQuotaLevel()
    {
        return $this->quotaLevel;
    }

    /**
     * returns the maxUserQuota
     *
     * @return integer
     */
    public function getMaxUserQuota()
    {
        return $this->maxUserQuota;
    }

    /**
     * return the maxQuota
     *
     * @return integer
     */
    public function getMaxQuota()
    {
        return $this->maxQuota;
    }
}
