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
 *
 * @method Config setQuery(string $query)
 * @method string|null getQuery()
 * @method bool hasQuery()
 * @method Config setResults(array $results)
 * @method array getResults()
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
