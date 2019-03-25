<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Search\Adapter\Mysql\Query\Builder;

use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match as BuilderMatch;
use Magento\Search\Helper\Data;

/**
 * @inheritdoc
 */
class Match extends BuilderMatch
{
    /**
     * @var Data
     */
    private $searchHelper;

    /**
     * @param ResolverInterface $resolver
     * @param Fulltext $fulltextHelper
     * @param Data $searchHelper
     * @param string $fulltextSearchMode
     * @param PreprocessorInterface[] $preprocessors
     */
    public function __construct(
        ResolverInterface $resolver,
        Fulltext $fulltextHelper,
        Data $searchHelper,
        $fulltextSearchMode = Fulltext::FULLTEXT_MODE_BOOLEAN,
        array $preprocessors = []
    ) {
        parent::__construct($resolver, $fulltextHelper, $fulltextSearchMode, $preprocessors);
        $this->searchHelper = $searchHelper;
    }

    /**
     * @inheritdoc
     */
    protected function getMinimalCharacterLength()
    {
        return $this->searchHelper->getMinQueryLength();
    }
}
