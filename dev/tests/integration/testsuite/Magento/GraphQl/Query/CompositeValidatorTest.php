<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Query;

use Magento\Framework\App\Area;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Validator\CompositeValidator;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Verify that CompositeValidator correctly delegates to all registered validators.
 *
 * Integration test for composites per DoD: ensures SearchCriteriaValidator and
 * BackpressureFieldValidator are loaded and functional within the composite.
 *
 * Regression: module-level di.xml array arguments replace global di.xml entries,
 * so SearchCriteriaValidator was silently dropped from CompositeValidator.
 */
#[
    CoversClass(CompositeValidator::class),
    AppArea(Area::AREA_GRAPHQL),
    DbIsolation(false),
]
class CompositeValidatorTest extends TestCase
{
    /**
     * @var CompositeValidator
     */
    private CompositeValidator $compositeValidator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->compositeValidator = Bootstrap::getObjectManager()->get(CompositeValidator::class);
    }

    /**
     * Verify SearchCriteriaValidator in composite enforces pageSize when limiting is enabled.
     */
    #[
        Config('graphql/validation/input_limit_enabled', 1, ScopeInterface::SCOPE_STORE, 'default'),
        Config('graphql/validation/maximum_page_size', 5, ScopeInterface::SCOPE_STORE, 'default')
    ]
    public function testSearchCriteriaValidatorEnforcesPageSizeLimit(): void
    {
        $field = $this->createMock(Field::class);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Maximum pageSize is 5');
        $this->compositeValidator->validate($field, ['pageSize' => 6]);
    }

    /**
     * Verify composite does not throw when pageSize is within the configured limit.
     *
     * @doesNotPerformAssertions
     */
    #[
        Config('graphql/validation/input_limit_enabled', 1, ScopeInterface::SCOPE_STORE, 'default'),
        Config('graphql/validation/maximum_page_size', 5, ScopeInterface::SCOPE_STORE, 'default')
    ]
    public function testCompositeAllowsPageSizeWithinLimit(): void
    {
        $field = $this->createMock(Field::class);

        $this->compositeValidator->validate($field, ['pageSize' => 5]);
    }

    /**
     * Verify composite does not throw when input limiting is disabled.
     *
     * @doesNotPerformAssertions
     */
    #[
        Config('graphql/validation/input_limit_enabled', 0, ScopeInterface::SCOPE_STORE, 'default'),
        Config('graphql/validation/maximum_page_size', 5, ScopeInterface::SCOPE_STORE, 'default')
    ]
    public function testSearchCriteriaValidatorSkipsEnforcementWhenDisabled(): void
    {
        $field = $this->createMock(Field::class);

        $this->compositeValidator->validate($field, ['pageSize' => 100]);
    }
}
