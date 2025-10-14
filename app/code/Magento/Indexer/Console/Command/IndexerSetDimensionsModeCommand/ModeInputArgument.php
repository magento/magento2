<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Console\Command\IndexerSetDimensionsModeCommand;

use Symfony\Component\Console\Input\InputArgument;

/**
 * InputArgument that takes callable for description instead of string
 */
class ModeInputArgument extends InputArgument
{

    /**
     * @var callable|null $callableDescription
     */
    private $callableDescription;

    /**
     *
     * @param string $name
     * @param int|null $mode
     * @param callable|null $callableDescription
     * @param string|bool|int|float|array|null $default
     */
    public function __construct(string $name, ?int $mode = null, ?callable $callableDescription = null, $default = null)
    {
        $this->callableDescription = $callableDescription;
        parent::__construct($name, $mode, '', $default);
    }

    /**
     * @inheritDoc
     */
    public function getDescription():string
    {
        if (null !== $this->callableDescription) {
            $description = ($this->callableDescription)();
            $this->callableDescription = null;
            return $description;
        }
        return parent::getDescription();
    }
}
