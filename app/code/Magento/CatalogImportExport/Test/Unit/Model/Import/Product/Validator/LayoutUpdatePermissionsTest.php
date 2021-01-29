<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Authorization\Model\UserContextInterface;
use Magento\CatalogImportExport\Model\Import\Product\Validator\LayoutUpdatePermissions;
use Magento\Framework\AuthorizationInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Test validation for layout update permissions
 */
class LayoutUpdatePermissionsTest extends TestCase
{
    /**
     * @var LayoutUpdatePermissions|MockObject
     */
    private $validator;

    /**
     * @var UserContextInterface|MockObject
     */
    private $userContext;

    /**
     * @var AuthorizationInterface|MockObject
     */
    private $authorization;

    /**
     * @var Product
     */
    private $context;

    protected function setUp(): void
    {
        $this->userContext = $this->getMockForAbstractClass(UserContextInterface::class);
        $this->authorization = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $this->context = $this->createMock(Product::class);
        $this->context
            ->method('retrieveMessageTemplate')
            ->with('insufficientPermissions')
            ->willReturn('oh no');
        $this->validator = new LayoutUpdatePermissions(
            $this->userContext,
            $this->authorization
        );
        $this->validator->init($this->context);
    }

    /**
     * @param $value
     * @param $userContext
     * @param $isAllowed
     * @param $isValid
     * @dataProvider configurationsProvider
     */
    public function testValidationConfiguration($value, $userContext, $isAllowed, $isValid)
    {
        $this->userContext
            ->method('getUserType')
            ->willReturn($userContext);

        $this->authorization
            ->method('isAllowed')
            ->with('Magento_Catalog::edit_product_design')
            ->willReturn($isAllowed);

        $result = $this->validator->isValid(['custom_layout_update' => $value]);
        $messages = $this->validator->getMessages();

        self::assertSame($isValid, $result);

        if ($isValid) {
            self::assertSame([], $messages);
        } else {
            self::assertSame(['oh no'], $messages);
        }
    }

    public function configurationsProvider()
    {
        return [
            ['', null, null, true],
            [null, null, null, true],
            ['foo', UserContextInterface::USER_TYPE_ADMIN, true, true],
            ['foo', UserContextInterface::USER_TYPE_INTEGRATION, true, true],
            ['foo', UserContextInterface::USER_TYPE_ADMIN, false, false],
            ['foo', UserContextInterface::USER_TYPE_INTEGRATION, false, false],
            ['foo', 'something', null, false],
        ];
    }
}
