<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Search;

use Magento\Backend\Model\Search\Config\Result\Builder;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\AbstractComposite;
use Magento\Config\Model\Config\Structure\Element\Iterator as ElementIterator;

/**
 * Search Config Model
  */
class Config extends \Magento\Framework\DataObject
{
    /**
     * @var \Magento\Framework\App\Config\ConfigTypeInterface
     */
    private $configStructure;

    /**
     * @var Builder
     */
    private $resultBuilder;

    /**
     * @param Structure $configStructure
     * @param Builder $resultBuilder
     */
    public function __construct(Structure $configStructure, Builder $resultBuilder)
    {
        $this->configStructure = $configStructure;
        $this->resultBuilder = $resultBuilder;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->setData('query', $query);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getQuery()
    {
        return $this->getData('query');
    }

    /**
     * @return bool
     */
    public function hasQuery()
    {
        return $this->hasData('query');
    }

    /**
     * @param array $results
     * @return $this
     */
    public function setResults(array $results)
    {
        $this->setData('results', $results);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getResults()
    {
        return $this->getData('results');
    }

    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $this->findInStructure($this->configStructure->getTabs(), $this->getQuery());
        $this->setResults($this->resultBuilder->getAll());
        return $this;
    }

    /**
     * @param ElementIterator $structureElementIterator
     * @param string $searchTerm
     * @param string $pathLabel
     * @return void
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function findInStructure(ElementIterator $structureElementIterator, $searchTerm, $pathLabel = '')
    {
        if (empty($searchTerm)) {
            return;
        }
        foreach ($structureElementIterator as $structureElement) {
            if (mb_stripos((string)$structureElement->getLabel(), $searchTerm) !== false) {
                $this->resultBuilder->add($structureElement, $pathLabel);
            }
            $elementPathLabel = $pathLabel . ' / ' . $structureElement->getLabel();
            if ($structureElement instanceof AbstractComposite && $structureElement->hasChildren()) {
                $this->findInStructure($structureElement->getChildren(), $searchTerm, $elementPathLabel);
            }
        }
    }
}
