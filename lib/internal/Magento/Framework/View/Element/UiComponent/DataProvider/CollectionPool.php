<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

/**
 * Class CollectionPool
 */
class CollectionPool
{
    /**
     * @var AbstractCollection[]
     */
    protected $collections;

    public function __construct(array $collections  = [])
    {
        $this->collections = $collections;
    }

    /**
     * @param $requestName
     * @return AbstractCollection
     * @throws \Exception
     */
    public function getCollection($requestName)
    {
        if (!isset($this->collections[$requestName])) {
            throw new \Exception('111111');
        }
        return $this->collections[$requestName];
    }
}