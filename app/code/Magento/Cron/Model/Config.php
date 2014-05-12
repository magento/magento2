<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cron\Model;

/**
 * Configuration entry point for client using
 */
class Config implements \Magento\Cron\Model\ConfigInterface
{
    /**
     * Cron config data
     *
     * @var \Magento\Cron\Model\Config\Data
     */
    protected $_configData;

    /**
     * Initialize needed parameters
     *
     * @param \Magento\Cron\Model\Config\Data $configData
     */
    public function __construct(\Magento\Cron\Model\Config\Data $configData)
    {
        $this->_configData = $configData;
    }

    /**
     * Return cron full cron jobs
     *
     * @return array
     */
    public function getJobs()
    {
        return $this->_configData->getJobs();
    }
}
