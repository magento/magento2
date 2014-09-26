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
namespace Magento\Rss\Model;

use \Magento\Framework\App\Rss\DataProviderInterface;
use \Magento\Framework\App\Rss\RssManagerInterface;

/**
 * Rss Manager
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class RssManager implements RssManagerInterface
{
    /**
     * @var \Magento\Framework\App\Rss\DataProviderInterface[]
     */
    protected $providers;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param array $dataProviders
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager, array $dataProviders = array())
    {
        $this->objectManager = $objectManager;
        $this->providers = $dataProviders;
    }

    /**
     * Return Rss Data Provider by Rss Feed Id.
     *
     * @param string $type
     * @return DataProviderInterface
     * @throws \InvalidArgumentException
     */
    public function getProvider($type)
    {
        if (!isset($this->providers[$type])) {
            throw new \InvalidArgumentException('Unknown provider with type: ' . $type);
        }

        $provider = $this->providers[$type];

        if (is_string($provider)) {
            $provider = $this->objectManager->get($provider);
        }

        if (!$provider instanceof DataProviderInterface) {
            throw new \InvalidArgumentException('Provider should implement DataProviderInterface');
        }

        $this->providers[$type] = $provider;

        return $this->providers[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function getProviders()
    {
        $result = array();
        foreach (array_keys($this->providers) as $type) {
            $result[] = $this->getProvider($type);
        }
        return $result;
    }
}
