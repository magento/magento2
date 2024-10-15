<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\Validation;

use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class StoreValidatorTest extends TestCase
{
    /**
     * @var StoreValidator
     */
    private $storeValidator;

    protected function setUp(): void
    {
        $this->storeValidator =  Bootstrap::getObjectManager()->create(StoreValidator::class);
    }

    /**
     * @dataProvider isValidDataProvider
     * @param Store $store
     * @param bool $isValid
     */
    public function testIsValid(Store $store, bool $isValid): void
    {
        $result = $this->storeValidator->isValid($store);
        $this->assertEquals($isValid, $result);
    }

    public static function isValidDataProvider(): array
    {
        $validStore = Bootstrap::getObjectManager()->create(Store::class);
        $validStore->setName('name');
        $validStore->setCode('code');
        $emptyStore = Bootstrap::getObjectManager()->create(Store::class);
        $storeWithEmptyName = Bootstrap::getObjectManager()->create(Store::class);
        $storeWithEmptyName->setCode('code');
        $storeWithEmptyCode = Bootstrap::getObjectManager()->create(Store::class);
        $storeWithEmptyCode->setName('name');
        $storeWithInvalidCode = Bootstrap::getObjectManager()->create(Store::class);
        $storeWithInvalidCode->setName('name');
        $storeWithInvalidCode->setCode('5');

        return [
            [$validStore, true],
            [$emptyStore, false],
            [$storeWithEmptyName, false],
            [$storeWithEmptyCode, false],
            [$storeWithInvalidCode, false],
        ];
    }
}
