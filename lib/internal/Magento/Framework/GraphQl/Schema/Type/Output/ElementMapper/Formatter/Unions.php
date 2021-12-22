<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Output\ElementMapper\Formatter;

use Magento\Framework\GraphQl\Config\Element\UnionType;
use Magento\Framework\GraphQl\Config\ConfigElementInterface;
use Magento\Framework\GraphQl\Schema\Type\OutputTypeInterface;
use Magento\Framework\GraphQl\Schema\Type\Output\ElementMapper\FormatterInterface;
use Magento\Framework\GraphQl\Schema\Type\Output\OutputMapper;

/**
 * Add unions implemented by type if configured.
 */
class Unions implements FormatterInterface
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
     * @inheritDoc
     */
    public function format(ConfigElementInterface $configElement, OutputTypeInterface $outputType): array
    {
        $config = [];
        if ($configElement instanceof UnionType && !empty($configElement->getTypes())) {
            $unionTypes = [];
            foreach ($configElement->getTypes() as $unionName) {
                $unionTypes[$unionName] = $this->outputMapper->getOutputType($unionName);
            }
            $config['types'] = $unionTypes;
        }

        return $config;
    }
}
