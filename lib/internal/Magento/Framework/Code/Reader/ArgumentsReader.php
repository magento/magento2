<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Reader;

use Magento\Framework\GetParameterClassTrait;
use Laminas\Code\Reflection\ParameterReflection;

/**
 * The class arguments reader
 */
class ArgumentsReader extends ParameterReflection
{
    use GetParameterClassTrait;

    public const NO_DEFAULT_VALUE = 'NO-DEFAULT';

    /**
     * @var NamespaceResolver
     */
    private $namespaceResolver;

    /**
     * @var ScalarTypesProvider
     */
    private $scalarTypesProvider;

    /**
     * @var ParameterReflection
     */
    protected $parameterReflection;

    /**
     * @param NamespaceResolver|null $namespaceResolver
     * @param ScalarTypesProvider|null $scalarTypesProvider
     */
    public function __construct(
        ?NamespaceResolver $namespaceResolver = null,
        ?ScalarTypesProvider $scalarTypesProvider = null
    ) {
        $this->namespaceResolver = $namespaceResolver ?: new NamespaceResolver();
        $this->scalarTypesProvider = $scalarTypesProvider ?: new ScalarTypesProvider();
    }

    /**
     * Get class constructor
     *
     * @param \ReflectionClass $class
     * @param bool $groupByPosition
     * @param bool $inherited
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getConstructorArguments(\ReflectionClass $class, $groupByPosition = false, $inherited = false)
    {
        $output = [];
        /**
         * Skip native PHP types, classes without constructor
         */
        if ($class->isInterface() || !$class->getFileName() || false == $class->hasMethod(
            '__construct'
        ) || !$inherited && $class->getConstructor()->class != $class->getName()
        ) {
            return $output;
        }

        $constructor = new \Laminas\Code\Reflection\MethodReflection($class->getName(), '__construct');
        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            $position = $parameter->getPosition();
            $index = $groupByPosition ? $position : $name;
            $default = null;
            if ($parameter->isOptional()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $value = $parameter->getDefaultValue();
                    if (true == is_array($value)) {
                        $default = $this->_varExportMin($value);
                    } elseif (true == is_int($value)) {
                        $default = $value;
                    } else {
                        $default = $parameter->getDefaultValue();
                    }
                } elseif ($parameter->allowsNull()) {
                    $default = null;
                }
            }

            $output[$index] = [
                'name' => $name,
                'position' => $position,
                'type' => $this->processType($class, $parameter),
                'isOptional' => $parameter->isOptional(),
                'default' => $default,
            ];
        }
        return $output;
    }

    /**
     * Process argument type.
     *
     * @param \ReflectionClass $class
     * @param \Laminas\Code\Reflection\ParameterReflection $parameter
     * @return string
     */
    private function processType(\ReflectionClass $class, \Laminas\Code\Reflection\ParameterReflection $parameter)
    {
        $this->parameterReflection = $parameter;
        $parameterClass = $this->getParameterClass($parameter);

        if ($parameterClass) {
            return NamespaceResolver::NS_SEPARATOR . $parameterClass->getName();
        }

        $type = $this->detectType();

        /**
         * $type === null if it is unspecified
         * $type === 'null' if it is used in doc block
         */
        if ($type === null || $type === 'null') {
            return null;
        }

        if (strpos($type, '[]') !== false) {
            return 'array';
        }

        if (!in_array($type, $this->scalarTypesProvider->getTypes())) {
            $availableNamespaces = $this->namespaceResolver->getImportedNamespaces(file($class->getFileName()));
            $availableNamespaces[0] = $class->getNamespaceName();
            return $this->namespaceResolver->resolveNamespace($type, $availableNamespaces);
        }

        return $type;
    }

    /**
     * Get arguments of parent __construct call
     *
     * @param \ReflectionClass $class
     * @param array $classArguments
     * @return array|null
     * @throws \ReflectionException
     */
    public function getParentCall(\ReflectionClass $class, array $classArguments): ?array
    {
        /** Skip native PHP types */
        if (!$class->getFileName()) {
            return null;
        }

        $trimFunction = function (&$value) {
            $position = strpos($value, ':');
            if ($position !== false) {
                $value = trim(substr($value, 0, $position), PHP_EOL . ' ');
            } else {
                $value = trim($value, PHP_EOL . ' $');
            }
        };

        $method = $class->getMethod('__construct');
        $start = $method->getStartLine();
        $end = $method->getEndLine();
        $length = $end - $start;

        $source = file($class->getFileName());
        $content = implode('', array_slice($source, $start, $length));
        $pattern = '/parent::__construct\(([ ' .
            PHP_EOL .
            ']*' .
            '([a-zA-Z0-9_]+([ ' . PHP_EOL . '])*:([ ' . PHP_EOL . '])*)*[$][a-zA-Z0-9_]*,)*[ ' .
            PHP_EOL .
            ']*' .
            '([a-zA-Z0-9_]+([ ' . PHP_EOL . '])*:([ ' . PHP_EOL . '])*)*([$][a-zA-Z0-9_]*)[' .
            PHP_EOL .
            ' ]*\);/';

        if (!preg_match($pattern, $content, $matches)) {
            return null;
        }

        $arguments = $matches[0];
        if (!trim($arguments)) {
            return null;
        }

        $arguments = substr(trim($arguments), 20, -2);
        $arguments = explode(',', $arguments);
        $isNamedArgument = [];
        foreach ($arguments as $argumentPosition => $argumentName) {
            $isNamedArgument[$argumentPosition] = (bool)strpos($argumentName, ':');
        }
        array_walk($arguments, $trimFunction);

        $output = [];
        foreach ($arguments as $argumentPosition => $argumentName) {
            $type = isset($classArguments[$argumentName]) ? $classArguments[$argumentName]['type'] : null;
            $output[$argumentPosition] = [
                'name' => $argumentName,
                'position' => $argumentPosition,
                'type' => $type,
                'isNamedArgument' => $isNamedArgument[$argumentPosition],
            ];
        }

        return $output;
    }

    /**
     * Check argument type compatibility
     *
     * @param string $requiredType
     * @param string $actualType
     * @return bool
     */
    public function isCompatibleType($requiredType, $actualType)
    {
        /** Types are compatible if type names are equal */
        if ($requiredType === $actualType) {
            return true;
        }

        /** Types are 'semi-compatible' if one of them are undefined */
        if ($requiredType === null || $actualType === null) {
            return true;
        }

        /**
         * Special case for scalar arguments
         * Array type is compatible with array or null type. Both of these types are checked above
         */
        if ($requiredType === 'array' || $actualType === 'array') {
            return false;
        }

        if ($requiredType === 'mixed' || $actualType === 'mixed') {
            return true;
        }

        return is_subclass_of($actualType, $requiredType);
    }

    /**
     * Export variable value
     *
     * @param mixed $var
     * @return mixed|string
     */
    protected function _varExportMin($var)
    {
        if (is_array($var)) {
            $toImplode = [];
            foreach ($var as $key => $value) {
                $toImplode[] = var_export($key, true) . ' => ' . $this->_varExportMin($value);
            }
            $code = 'array(' . implode(', ', $toImplode) . ')';
            return $code;
        } else {
            return var_export($var, true);
        }
    }

    /**
     * Get constructor annotations
     *
     * @param \ReflectionClass $class
     * @return array
     */
    public function getAnnotations(\ReflectionClass $class)
    {
        $regexp = '(@([a-z_][a-z0-9_]+)\(([^\)]+)\))i';
        $docBlock = $class->getConstructor()->getDocComment();
        $annotations = [];
        preg_match_all($regexp, $docBlock, $matches);
        foreach (array_keys($matches[0]) as $index) {
            $name = $matches[1][$index];
            $value = trim($matches[2][$index], '" ');
            $annotations[$name] = $value;
        }

        return $annotations;
    }

    /**
     * ReflectionType does not have an isBuiltin() / getName() method
     *
     * @deprecated this method is unreliable, and should not be used: it will be removed in the next major release.
     *             It may crash on parameters with union types, and will return relative types, instead of
     *             FQN references
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @see reflectionnamedtype.isbuiltin.php
     *
     * @return mixed|string|void|null
     */
    public function detectType()
    {
        if (null !== ($type = $this->parameterReflection->getType())
            && method_exists($type, 'isBuiltin') && $type->isBuiltin()
        ) {
            return $type->getName();
        }

        if (null !== $type && method_exists($type, 'getName') && $type->getName() === 'self') {
            $declaringClass = $this->parameterReflection->getDeclaringClass();
            // @codingStandardsIgnoreStart
            assert($declaringClass !== null, 'A parameter called `self` can only exist on a class');
            // @codingStandardsIgnoreEnd

            return $declaringClass->getName();
        }

        if (($class = $this->parameterReflection->getClass()) instanceof \ReflectionClass) {
            return $class->getName();
        }

        $docBlock = $this->parameterReflection->getDeclaringFunction()->getDocBlock();

        if (! $docBlock instanceof \Laminas\Code\Reflection\DocBlockReflection) {
            return null;
        }

        $params       = $docBlock->getTags('param');
        $paramTag     = $params[$this->parameterReflection->getPosition()] ?? null;
        $variableName = '$' . $this->parameterReflection->getName();

        if ($paramTag && ('' === $paramTag->getVariableName() || $variableName === $paramTag->getVariableName())) {
            return $paramTag->getTypes()[0] ?? '';
        }

        foreach ($params as $param) {
            if ($param->getVariableName() === $variableName) {
                return $param->getTypes()[0] ?? '';
            }
        }

        return null;
    }
}
