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
 * @version    $Id: ChangeQuotaPool.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Request_BaseUserService_ChangeQuotaPool
{
    /**
     * string module id
     *
     * @var string
     */
    public $moduleId = null;

    /**
     * integer >= 0 to set new user quota
     *
     * @var integer
     */
    public $quotaMax = 0;

    /**
     * constructor give them the module id
     *
     * @param string $moduleId
     * @param integer $quotaMax
     * @return Zend_Service_Developergarde_Request_ChangeQuotaPool
     */
    public function __construct($moduleId = null, $quotaMax = 0)
    {
        $this->setModuleId($moduleId)
             ->setQuotaMax($quotaMax);
    }

    /**
     * sets a new moduleId
     *
     * @param integer $moduleId
     * @return Zend_Service_Developergarde_Request_ChangeQuotaPool
     */
    public function setModuleId($moduleId = null)
    {
        $this->moduleId = $moduleId;
        return $this;
    }

    /**
     * returns the moduleId
     *
     * @return string
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /**
     * sets new QuotaMax value
     *
     * @param integer $quotaMax
     * @return Zend_Service_Developergarde_Request_ChangeQuotaPool
     */
    public function setQuotaMax($quotaMax = 0)
    {
        $this->quotaMax = $quotaMax;
        return $this;
    }

    /**
     * returns the quotaMax value
     *
     * @return integer
     */
    public function getQuotaMax()
    {
        return $this->quotaMax;
    }
}
