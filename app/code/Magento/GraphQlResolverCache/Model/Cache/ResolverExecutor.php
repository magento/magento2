<?php

namespace Magento\GraphQlResolverCache\Model\Cache;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\ValueProcessorInterface;

class ResolverExecutor
{
    /**
     * @var \Closure
     */
    private \Closure $resolveMethod;

    /**
     * @var ValueProcessorInterface
     */
    private ValueProcessorInterface $valueProcessor;

    /**
     * @param \Closure $resolveMethod
     * @param ValueProcessorInterface $valueProcessor
     */
    public function __construct(
        \Closure $resolveMethod,
        ValueProcessorInterface $valueProcessor
    ) {
        $this->resolveMethod = $resolveMethod;
        $this->valueProcessor = $valueProcessor;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        ResolverInterface $resolverSubject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $preprocessValue = true;
        foreach ($this->getResolverClassChain($resolverSubject) as $class) {
            if (isset($this->config['skipValuePreprocessing'][$class])) {
                $preprocessValue = false;
                break;
            }
        }

        if ($preprocessValue) {
            $this->valueProcessor->preProcessParentResolverValue($value);
        }
        return ($this->resolveMethod)($field, $context, $info, $value, $args);
    }

    /**
     * Get class inheritance chain for the given resolver object.
     *
     * @param ResolverInterface $resolver
     * @return array
     */
    private function getResolverClassChain(ResolverInterface $resolver): array
    {
        $resolverClasses = [trim(get_class($resolver), '\\')];
        foreach (class_parents($resolver) as $classParent) {
            $resolverClasses[] = trim($classParent, '\\');
        }
        return $resolverClasses;
    }
}
