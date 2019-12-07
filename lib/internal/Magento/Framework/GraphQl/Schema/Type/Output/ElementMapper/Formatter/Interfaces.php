<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Output\ElementMapper\Formatter;

use Magento\Framework\GraphQl\Config\Element\Type;
use Magento\Framework\GraphQl\Config\Element\TypeInterface;
use Magento\Framework\GraphQl\Schema\Type\OutputTypeInterface;
use Magento\Framework\GraphQl\Schema\Type\Output\ElementMapper\FormatterInterface;
use Magento\Framework\GraphQl\Schema\Type\Output\OutputMapper;

/**
 * Add interfaces implemented by type if configured.
 */
class Interfaces implements FormatterInterface
{
    /**
     * @var OutputMapper
     */
    private $outputMapper;

    /**
     * @param OutputMapper $outputMapper
     */
    public function __construct(OutputMapper $outputMapper)
    {
        $this->outputMapper = $outputMapper;
    }

    /**
     * {@inheritDoc}
     */
    public function format(TypeInterface $configElement, OutputTypeInterface $outputType) : array
    {
        $config = [];
        if ($configElement instanceof Type && !empty($configElement->getInterfaces())) {
            $interfaces = [];
            foreach ($configElement->getInterfaces() as $interface) {
                $interfaces[$interface['interface']] = $this->outputMapper->getOutputType($interface['interface']);
            }
            $config['interfaces'] = $interfaces;
        }

        return $config;
    }
}
