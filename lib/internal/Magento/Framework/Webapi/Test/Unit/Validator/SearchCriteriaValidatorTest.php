<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Validator;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Validator\SearchCriteriaValidator;
use PHPUnit\Framework\TestCase;

/**
 * Verifies behavior of the search criteria validator
 */
class SearchCriteriaValidatorTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testAllowsPageSizeWhenAboveMinLimitAndBelowMaxLimit()
    {
        $searchCriteria = new SearchCriteria();
        $validator = new SearchCriteriaValidator(3);
        $validator->validateEntityValue($searchCriteria, 'pageSize', 2);
    }

    public function testFailsPageSizeWhenAboveMaxLimit()
    {
        $this->expectException(LocalizedException::class);
        $this->expectErrorMessage('Maximum SearchCriteria pageSize is 3');
        $searchCriteria = new SearchCriteria();
        $validator = new SearchCriteriaValidator(3);
        $validator->validateEntityValue($searchCriteria, 'pageSize', 4);
    }
}
