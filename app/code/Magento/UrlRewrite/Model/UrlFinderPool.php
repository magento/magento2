<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class UrlFinderPool
 */
class UrlFinderPool implements \Iterator
{
    private const SORT_KEY = 'sortOrder';

    /**
     * @var array
     */
    private $urlFinders;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param array $urlFinders
     */
    public function __construct(array $urlFinders, ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->setUrlFinders($urlFinders);
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        $current = current($this->urlFinders);
        return $this->objectManager->create($current['class']);
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        return next($this->urlFinders);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        return reset($this->urlFinders);
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return (bool)current($this->urlFinders);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return key($this->urlFinders);
    }

    /**
     * Set and sort the urlFinders
     *
     * @param array $urlFinders
     * @return void
     */
    public function setUrlFinders(array $urlFinders)
    {
        $this->urlFinders = $urlFinders;
        $this->sortFinders();
    }

    /**
     * Sort UrlFinders by sortOrder
     *
     * @return void
     */
    private function sortFinders()
    {
        uasort($this->urlFinders, function ($first, $second) {
            return (int)$first[self::SORT_KEY] <=> (int)$second[self::SORT_KEY];
        });
    }
}
