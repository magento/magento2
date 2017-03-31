<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

/**
 * Class SearchTermDescriptionGenerator
 *
 * Class responsible for generation description
 * and applying search terms to it
 */
class SearchTermDescriptionGenerator implements DescriptionGeneratorInterface
{
    /**
     * @var \Magento\Setup\Model\Description\DescriptionGenerator
     */
    private $descriptionGenerator;

    /**
     * @var \Magento\Setup\Model\SearchTermManager
     */
    private $searchTermManager;

    /**
     * @var string
     */
    private $cachedDescription;

    /**
     * @param \Magento\Setup\Model\Description\DescriptionGenerator $descriptionGenerator
     * @param \Magento\Setup\Model\SearchTermManager $searchTermManager
     */
    public function __construct(
        \Magento\Setup\Model\Description\DescriptionGenerator $descriptionGenerator,
        \Magento\Setup\Model\SearchTermManager $searchTermManager
    ) {
        $this->descriptionGenerator = $descriptionGenerator;
        $this->searchTermManager = $searchTermManager;
    }

    /**
     * Generate description with search terms
     *
     * @param int $currentProductIndex
     * @return string
     */
    public function generate($currentProductIndex)
    {
        $description = $this->getDescription();
        $this->searchTermManager->applySearchTermsToDescription($description, (int) $currentProductIndex);

        return $description;
    }

    /**
     * Generate new description or use cached one
     *
     * @param bool $useCachedDescription
     * @return string
     */
    private function getDescription($useCachedDescription = true)
    {
        if ($useCachedDescription !== true || $this->cachedDescription === null) {
            $this->cachedDescription = $this->descriptionGenerator->generate();
        }

        return $this->cachedDescription;
    }
}
