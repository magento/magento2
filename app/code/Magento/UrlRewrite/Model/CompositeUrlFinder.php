<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Model;

use Magento\Framework\ObjectManagerInterface;
use \Magento\UrlRewrite\Model\MergeDataProviderFactory;

/**
 * Class CompositeUrlFinder
 */
class CompositeUrlFinder implements UrlFinderInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $children = [];

    /**
     * @var MergeDataProviderFactory
     */
    private $mergeDataProviderFactory;

    /**
     * @param array $children
     * @param ObjectManagerInterface $objectManager
     * @param MergeDataProviderFactory $mergeDataProviderFactory
     */
    public function __construct(
        array $children,
        ObjectManagerInterface $objectManager,
        MergeDataProviderFactory $mergeDataProviderFactory
    ) {
        $this->children = $children;
        $this->objectManager = $objectManager;
        $this->mergeDataProviderFactory = $mergeDataProviderFactory;
    }

    /**
     * @inheritdoc
     */
    public function findAllByData(array $data)
    {
        $mergeDataProvider = $this->mergeDataProviderFactory->create();
        foreach ($this->getChildren() as $child) {
            $urlFinder = $this->objectManager->get($child['class']);
            $mergeDataProvider->merge($urlFinder->findAllByData($data));
        }
        return $mergeDataProvider->getData();
    }

    /**
     * @inheritdoc
     */
    public function findOneByData(array $data)
    {
        foreach ($this->getChildren() as $child) {
            $urlFinder = $this->objectManager->get($child['class']);
            $rewrite = $urlFinder->findOneByData($data);
            if (!empty($rewrite)) {
                return $rewrite;
            }
        }
        return null;
    }

    /**
     * Get children in sorted order
     *
     * @return array
     */
    private function getChildren()
    {
        uasort($this->children, function ($first, $second) {
            return (int)$first['sortOrder'] <=> (int)$second['sortOrder'];
        });
        return $this->children;
    }
}
