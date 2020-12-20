<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Attribute\Backend;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Attribute\Backend\DefaultBackend;
use Magento\Framework\DataObject;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\HTML\WYSIWYGValidatorInterface;
use PHPUnit\Framework\TestCase;
use Magento\Eav\Model\Entity\Attribute as BasicAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Exception as AttributeException;

class DefaultBackendTest extends TestCase
{
    /**
     * Different cases for attribute validation.
     *
     * @return array
     */
    public function getAttributeConfigurations(): array
    {
        return [
            'basic-attribute' => [true, false, true, 'basic', 'value', false, true, false],
            'non-html-attribute' => [false, false, false, 'non-html', 'value', false, false, false],
            'empty-html-attribute' => [false, false, true, 'html', null, false, true, false],
            'invalid-html-attribute' => [false, false, false, 'html', 'value', false, true, true],
            'valid-html-attribute' => [false, true, false, 'html', 'value', false, true, false],
            'changed-invalid-html-attribute' => [false, false, true, 'html', 'value', true, true, true],
            'changed-valid-html-attribute' => [false, true, true, 'html', 'value', true, true, false]
        ];
    }

    /**
     * Test attribute validation.
     *
     * @param bool $isBasic
     * @param bool $isValidated
     * @param bool $isCatalogEntity
     * @param string $code
     * @param mixed $value
     * @param bool $isChanged
     * @param bool $isHtmlAttribute
     * @param bool $exceptionThrown
     * @dataProvider getAttributeConfigurations
     */
    public function testValidate(
        bool $isBasic,
        bool $isValidated,
        bool $isCatalogEntity,
        string $code,
        $value,
        bool $isChanged,
        bool $isHtmlAttribute,
        bool $exceptionThrown
    ): void {
        if ($isBasic) {
            $attributeMock = $this->createMock(BasicAttribute::class);
        } else {
            $attributeMock = $this->createMock(Attribute::class);
            $attributeMock->expects($this->any())
                ->method('getIsHtmlAllowedOnFront')
                ->willReturn($isHtmlAttribute);
        }
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($code);

        $validatorMock = $this->getMockForAbstractClass(WYSIWYGValidatorInterface::class);
        if (!$isValidated) {
            $validatorMock->expects($this->any())
                ->method('validate')
                ->willThrowException(new ValidationException(__('HTML is invalid')));
        } else {
            $validatorMock->expects($this->any())->method('validate');
        }

        if ($isCatalogEntity) {
            $objectMock = $this->createMock(AbstractModel::class);
            $objectMock->expects($this->any())
                ->method('getOrigData')
                ->willReturn($isChanged ? $value .'-OLD' : $value);
        } else {
            $objectMock = $this->createMock(DataObject::class);
        }
        $objectMock->expects($this->any())->method('getData')->with($code)->willReturn($value);

        $model = new DefaultBackend($validatorMock);
        $model->setAttribute($attributeMock);

        $actuallyThrownForSave = false;
        try {
            $model->beforeSave($objectMock);
        } catch (AttributeException $exception) {
            $actuallyThrownForSave = true;
        }
        $actuallyThrownForValidate = false;
        try {
            $model->validate($objectMock);
        } catch (AttributeException $exception) {
            $actuallyThrownForValidate = true;
        }
        $this->assertEquals($actuallyThrownForSave, $actuallyThrownForValidate);
        $this->assertEquals($actuallyThrownForSave, $exceptionThrown);
    }
}
