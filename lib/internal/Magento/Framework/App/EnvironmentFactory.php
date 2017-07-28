<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\ObjectManager\Environment\Compiled;
use Magento\Framework\App\ObjectManager\Environment\Developer;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManager\RelationsInterface;
use Magento\Framework\ObjectManager\DefinitionInterface;

/**
 * Class \Magento\Framework\App\EnvironmentFactory
 *
 * @since 2.0.0
 */
class EnvironmentFactory
{
    /**
     * @var RelationsInterface
     * @since 2.0.0
     */
    private $relations;

    /**
     * @var DefinitionInterface
     * @since 2.0.0
     */
    private $definitions;

    /**
     * @param RelationsInterface $relations
     * @param DefinitionInterface $definitions
     * @since 2.0.0
     */
    public function __construct(
        RelationsInterface $relations,
        DefinitionInterface $definitions
    ) {
        $this->relations = $relations;
        $this->definitions = $definitions;
    }

    /**
     * Create Environment object
     *
     * @return EnvironmentInterface
     * @since 2.0.0
     */
    public function createEnvironment()
    {
        switch ($this->getMode()) {
            case Compiled::MODE:
                return new Compiled($this);
                break;
            default:
                return new Developer($this);
        }
    }

    /**
     * Determinate running mode
     *
     * @return string
     * @since 2.0.0
     */
    private function getMode()
    {
        if (file_exists(ConfigLoader\Compiled::getFilePath(Area::AREA_GLOBAL))) {
            return Compiled::MODE;
        }

        return Developer::MODE;
    }

    /**
     * Returns definitions
     *
     * @return DefinitionInterface
     * @since 2.0.0
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * Returns relations
     *
     * @return RelationsInterface
     * @since 2.0.0
     */
    public function getRelations()
    {
        return $this->relations;
    }
}
