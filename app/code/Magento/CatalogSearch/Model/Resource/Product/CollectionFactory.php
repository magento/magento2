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
namespace Magento\CatalogSearch\Model\Resource\Product;

class CollectionFactory
{
    const PRODUCT_COLLECTION_FULLTEXT = 'catalogSearchFulltextCollection';
    const PRODUCT_COLLECTION_ADVANCED = 'catalogSearchAdvancedCollection';

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Array of product collection factory names
     *
     * @var array
     */
    protected $productFactoryNames;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $productFactoryNames
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $productFactoryNames
    ) {
        $this->objectManager = $objectManager;
        $this->productFactoryNames = $productFactoryNames;
    }

    /**
     * Create collection instance with specified parameters
     *
     * @param string $collectionName
     * @param array $data
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    public function create($collectionName, array $data = array())
    {
        if (!isset($this->productFactoryNames[$collectionName])) {
            throw new \RuntimeException(sprintf('Collection "%s" has not been set', $collectionName));
        }
        $instance = $this->objectManager->create($this->productFactoryNames[$collectionName], $data);
        if (!$instance instanceof \Magento\Catalog\Model\Resource\Product\Collection) {
            throw new \RuntimeException(
                $this->productFactoryNames[$collectionName] .
                ' is not instance of \Magento\Catalog\Model\Resource\Product\Collection'
            );
        }
        return $instance;
    }
}
