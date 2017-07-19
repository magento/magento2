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
use Magento\Config\Model\Config\Structure\ElementInterface;

/**
 * Search Config Model
 *
 * @method string|null getQuery()
 * @method bool hasQuery()
 * @method Config setResults(array $results)
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
        if (!$this->hasQuery()) {
            $this->setResults($this->resultBuilder->getAll());
            return $this;
        }

        $this->findInStructure($this->configStructure->getTabs(), $this->getQuery());

        $this->setResults($this->resultBuilder->getAll());
        return $this;
    }

    /**
     * @param ElementIterator $structureElementIterator
     * @param string $needle
     * @param string $pathLabel
     */
    public function findInStructure(ElementIterator $structureElementIterator, $needle, $pathLabel = '')
    {
        foreach ($structureElementIterator as $structureElement) {
            if (!($structureElement instanceof ElementInterface)) {
                continue;
            }
            $elementPathLabel = $pathLabel . '/' . $structureElement->getLabel();
            if (stripos((string)$structureElement->getLabel(), $needle) !== false) {
                $this->resultBuilder->add($structureElement, $elementPathLabel);
            }
            if ($structureElement instanceof AbstractComposite && $structureElement->hasChildren()) {
                $this->findInStructure($structureElement->getChildren(), $needle, $elementPathLabel);
            }
        }
    }
}
