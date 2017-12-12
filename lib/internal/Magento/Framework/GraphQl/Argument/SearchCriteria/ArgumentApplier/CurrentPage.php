<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\SearchCriteria\ArgumentApplier;

use Magento\Framework\GraphQl\ArgumentInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Argument\SearchCriteria\ArgumentApplierInterface;
use Magento\Framework\Phrase;

/**
 * Class for CurrentPage Argument
 */
class CurrentPage implements ArgumentApplierInterface
{
    const ARGUMENT_NAME = 'currentPage';

    /**
     * {@inheritdoc}
     */
    public function applyArgument(SearchCriteriaInterface $searchCriteria, ArgumentInterface $argument)
    {
        if (is_int($argument->getValue()) || is_string($argument->getValue())) {
            $searchCriteria->setCurrentPage($argument->getValue());
        } else {
            throw new \Magento\Framework\Exception\RuntimeException(
                new Phrase('Argument %1 not of type Int', [$argument->getName()])
            );
        }
    }
}
