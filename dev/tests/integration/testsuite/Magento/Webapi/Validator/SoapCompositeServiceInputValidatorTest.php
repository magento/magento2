<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Webapi\Validator;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Webapi\Validator\CompositeServiceInputValidator;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Verify that module-global service input validators also reach SOAP.
 */
#[
    CoversClass(CompositeServiceInputValidator::class),
    AppArea(Area::AREA_WEBAPI_SOAP),
    DbIsolation(false),
]
class SoapCompositeServiceInputValidatorTest extends TestCase
{
    /**
     * @var CompositeServiceInputValidator
     */
    private CompositeServiceInputValidator $validator;

    protected function setUp(): void
    {
        $this->validator = Bootstrap::getObjectManager()->get(CompositeServiceInputValidator::class);
    }

    public function testCartItemRejectsNonPositiveQuantity(): void
    {
        $this->expectException(InputException::class);
        $this->validator->validateEntityValue($this->createStub(CartItemInterface::class), 'qty', 0);
    }

    #[
        Config('webapi/validation/input_limit_enabled', 1, ScopeInterface::SCOPE_STORE, 'default'),
        Config('webapi/validation/maximum_page_size', 5, ScopeInterface::SCOPE_STORE, 'default')
    ]
    public function testSearchCriteriaLimitIsStillEnforced(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum SearchCriteria pageSize is 5');
        $this->validator->validateEntityValue($this->createStub(SearchCriteriaInterface::class), 'pageSize', 6);
    }
}
