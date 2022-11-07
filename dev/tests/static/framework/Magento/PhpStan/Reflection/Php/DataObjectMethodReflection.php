<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PhpStan\Reflection\Php;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\Php\DummyParameter;
use PHPStan\TrinaryLogic;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataObjectMethodReflection implements MethodReflection
{
    private const PREFIX_LENGTH = 3;

    /**
     * @var ClassReflection
     */
    private $classReflection;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @param ClassReflection $classReflection
     * @param string $methodName
     */
    public function __construct(ClassReflection $classReflection, string $methodName)
    {
        $this->classReflection = $classReflection;
        $this->methodName = $methodName;
    }

    /**
     * Get methods class reflection.
     *
     * @return ClassReflection
     */
    public function getDeclaringClass(): ClassReflection
    {
        return $this->classReflection;
    }

    /**
     * Get if method is static.
     *
     * @return bool
     */
    public function isStatic(): bool
    {
        return false;
    }

    /**
     * Get if method is private.
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        return false;
    }

    /**
     * Get if method is public.
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        return true;
    }

    /**
     * Get Method PHP Doc comment message.
     *
     * @return string|null
     */
    public function getDocComment(): ?string
    {
        return null;
    }

    /**
     * Get Method Name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->methodName;
    }

    /**
     * Get Prototype.
     *
     * @return ClassMemberReflection
     */
    public function getPrototype(): ClassMemberReflection
    {
        return $this;
    }

    /**
     * Get Magic Methods variant based on type (get/set/has/uns).
     *
     * @return ParametersAcceptor[]
     */
    public function getVariants(): array
    {
        return [
            new FunctionVariant(
                TemplateTypeMap::createEmpty(),
                TemplateTypeMap::createEmpty(),
                $this->getMethodParameters(),
                false,
                $this->getReturnType()
            )
        ];
    }

    /**
     * Get prefix from method name.
     *
     * @return string
     */
    private function getMethodNamePrefix(): string
    {
        return (string)substr($this->methodName, 0, self::PREFIX_LENGTH);
    }

    /**
     * Get Magic Methods parameters.
     *
     * @return ParameterReflection[]
     */
    private function getMethodParameters(): array
    {
        $params = [];
        switch ($this->getMethodNamePrefix()) {
            case 'set':
                $params[] = new DummyParameter(
                    'value',
                    new MixedType(),
                    false,
                    null,
                    false,
                    null
                );
                break;
            case 'get':
                $params[] = new DummyParameter(
                    'index',
                    new UnionType([new StringType(), new IntegerType()]),
                    true,
                    null,
                    false,
                    null
                );
                break;
        }

        return $params;
    }

    /**
     * Get Magic Methods return type.
     *
     * @return Type
     */
    private function getReturnType(): Type
    {
        switch ($this->getMethodNamePrefix()) {
            case 'set':
            case 'uns':
                $returnType = new ObjectType($this->classReflection->getName());
                break;
            case 'has':
                $returnType = new BooleanType();
                break;
            default:
                $returnType = new MixedType();
                break;
        }

        return $returnType;
    }

    /**
     * Get if method is deprecated.
     *
     * @return TrinaryLogic
     */
    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    /**
     * Get deprecated description.
     *
     * @return string|null
     */
    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    /**
     * Get if method is final.
     *
     * @return TrinaryLogic
     */
    public function isFinal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    /**
     * Get if method is internal.
     *
     * @return TrinaryLogic
     */
    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    /**
     * Get method throw type.
     *
     * @return Type|null
     */
    public function getThrowType(): ?Type
    {
        return new VoidType();
    }

    /**
     * Get if method has side effect.
     *
     * @return TrinaryLogic
     */
    public function hasSideEffects(): TrinaryLogic
    {
        return TrinaryLogic::createYes();
    }
}
