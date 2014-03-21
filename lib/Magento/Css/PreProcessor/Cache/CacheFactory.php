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
namespace Magento\Css\PreProcessor\Cache;

use Magento\Css\PreProcessor\Cache\Import\Cache;

/**
 * Cache manager factory
 */
class CacheFactory
{
    /**
     * @var array
     */
    protected $cacheTypes = array(Cache::IMPORT_CACHE => 'Magento\Css\PreProcessor\Cache\Import\Cache');

    /**
     * @var \Magento\ObjectManager
     */
    protected $objectManager;

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $cacheType
     * @param \Magento\View\Publisher\FileInterface $publisherFile
     * @return CacheInterface
     * @throws \InvalidArgumentException
     */
    public function create($cacheType, $publisherFile)
    {
        if (!isset($this->cacheTypes[$cacheType])) {
            throw new \InvalidArgumentException(sprintf('No cache type registered for "%s" type.', $cacheType));
        }

        /** @var CacheInterface $cacheManager */
        $cacheManager = $this->objectManager->create(
            $this->cacheTypes[$cacheType],
            array('publisherFile' => $publisherFile)
        );

        if (!$cacheManager instanceof CacheInterface) {
            throw new \InvalidArgumentException(
                'Cache Manager does not implement \Magento\Css\PreProcessor\Cache\CacheInterface'
            );
        }

        return $cacheManager;
    }
}
