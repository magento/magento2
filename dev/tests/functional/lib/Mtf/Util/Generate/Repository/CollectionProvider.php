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

namespace Mtf\Util\Generate\Repository;

use Magento\Framework\ObjectManager;

/**
 * Class CollectionProvider
 *
 */
class CollectionProvider implements CollectionProviderInterface
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @constructor
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get collection by type which is specified in fixture .xml
     *
     * @param array $fixture
     * @return array
     */
    public function getCollection(array $fixture)
    {
        $type = $fixture['type'];
        $method = $type . 'Collection';
        if (!method_exists($this, $method)) {
            return [];
        }

        $collection = $this->$method($fixture);

        $keys = !empty($fixture['data_set']) && is_array($fixture['data_set'])
            ? array_keys($fixture['data_set'])
            : [];

        $items = [];
        foreach ($collection as $model) {
            /** @var $model \Magento\Framework\Object */
            $item = $model->toArray($keys);
            $item['id'] = $model->getId();
            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get collection of objects for which eav or flat type is not exist
     *
     * @param array $fixture
     * @return \Magento\Framework\Object[]
     */
    protected function tableCollection(array $fixture)
    {
        $collection = $fixture['collection'];
        $collection = $this->objectManager->create($collection, ['fixture' => $fixture]);
        /** @var $collection \Mtf\Util\Generate\Repository\TableCollection */
        return $collection->getItems();
    }

    /**
     * Get collection of objects for flat type
     *
     * @param array $fixture
     * @return \Magento\Framework\Object[]
     */
    protected function flatCollection(array $fixture)
    {
        $collection = $fixture['collection'];
        $collection = $this->objectManager->create($collection);
        /** @var $collection \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection */
        $collection->addFieldToSelect('*');

        return $collection->getItems();
    }

    /**
     * Get collection of objects for eav type
     *
     * @param array $fixture
     * @return \Magento\Framework\Object[]
     */
    protected function eavCollection(array $fixture)
    {
        $collection = $fixture['collection'];
        $collection = $this->objectManager->create($collection);
        /** @var $collection \Magento\Eav\Model\Entity\Collection\AbstractCollection */
        $collection->addAttributeToSelect('*');
        if (isset($fixture['product_type'])) {
            $collection->addAttributeToFilter('type_id', $fixture['product_type']);
        }

        return $collection->getItems();
    }
}
