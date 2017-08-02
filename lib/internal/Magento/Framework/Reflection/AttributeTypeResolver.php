<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Magento\Framework\Api\AttributeTypeResolverInterface;
use Magento\Framework\Api\ExtensionAttribute\Config;

/**
 * Class \Magento\Framework\Reflection\AttributeTypeResolver
 *
 * @since 2.0.0
 */
class AttributeTypeResolver implements AttributeTypeResolverInterface
{
    /**
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var TypeProcessor
     * @since 2.0.0
     */
    protected $typeProcessor;

    /**
     * @param TypeProcessor $typeProcessor
     * @param Config $config
     * @since 2.0.0
     */
    public function __construct(TypeProcessor $typeProcessor, Config $config)
    {
        $this->config = $config;
        $this->typeProcessor = $typeProcessor;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function resolveObjectType($attributeCode, $value, $context)
    {
        if (!is_object($value)) {
            throw new \InvalidArgumentException('Provided value is not object type');
        }
        $data = $this->config->get();
        $context = trim($context, '\\');
        $config = isset($data[$context]) ? $data[$context] : [];
        $output = get_class($value);
        if (isset($config[$attributeCode])) {
            $type = $config[$attributeCode]['type'];
            $output = $this->typeProcessor->getArrayItemType($type);
            if (!(class_exists($output) || interface_exists($output))) {
                throw new \LogicException(
                    sprintf('Class "%s" does not exist. Please note that namespace must be specified.', $type)
                );
            }
        }
        return $output;
    }
}
