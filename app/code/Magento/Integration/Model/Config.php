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
namespace Magento\Integration\Model;

use Magento\Integration\Model\Cache\Type;

/**
 * Integration Config Model.
 *
 * This is a parent class for storing information about Integrations.
 */
class Config
{
    const CACHE_ID = 'integration';

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_configCacheType;

    /**
     * @var \Magento\Integration\Model\Config\Reader
     */
    protected $_configReader;

    /**
     * Array of integrations
     *
     * @var array
     */
    protected $_integrations;

    /**
     * @param Cache\Type $configCacheType
     * @param Config\Reader $configReader
     */
    public function __construct(Cache\Type $configCacheType, Config\Reader $configReader)
    {
        $this->_configCacheType = $configCacheType;
        $this->_configReader = $configReader;
    }

    /**
     * Return integrations loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getIntegrations()
    {
        if (null === $this->_integrations) {
            $integrations = $this->_configCacheType->load(self::CACHE_ID);
            if ($integrations && is_string($integrations)) {
                $this->_integrations = unserialize($integrations);
            } else {
                $this->_integrations = $this->_configReader->read();
                $this->_configCacheType->save(serialize($this->_integrations), self::CACHE_ID, array(Type::CACHE_TAG));
            }
        }
        return $this->_integrations;
    }
}
