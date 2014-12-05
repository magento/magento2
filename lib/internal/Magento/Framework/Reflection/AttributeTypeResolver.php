<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
