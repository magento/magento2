<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Magento\Framework\Api\AttributeTypeResolverInterface;
use Magento\Framework\Api\Config\Reader;

class AttributeTypeResolver implements AttributeTypeResolverInterface
{
    /**
     * @var Reader
     */
    protected $configReader;

    /**
     * @var TypeProcessor
     */
    protected $typeProcessor;

    /**
     * @param TypeProcessor $typeProcessor
     * @param Reader $configReader
     */
    public function __construct(TypeProcessor $typeProcessor, Reader $configReader)
    {
        $this->configReader = $configReader;
        $this->typeProcessor = $typeProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveObjectType($attributeCode, $value, $context)
    {
        if (!is_object($value)) {
            throw new \InvalidArgumentException('Provided value is not object type');
        }
        $data = $this->configReader->read();
        $context = trim($context, '\\');
        $config = isset($data[$context]) ? $data[$context] : [];
        $output = get_class($value);
        if (isset($config[$attributeCode])) {
            $type = $config[$attributeCode];
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
