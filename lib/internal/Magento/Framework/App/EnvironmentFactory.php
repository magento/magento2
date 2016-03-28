<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\ObjectManager\Environment\Compiled;
use Magento\Framework\App\ObjectManager\Environment\Developer;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManager\RelationsInterface;
use Magento\Framework\ObjectManager\DefinitionInterface;

class EnvironmentFactory
{
    /**
     * @var RelationsInterface
     */
    private $relations;

    /**
     * @var DefinitionInterface
     */
    private $definitions;

    /**
     * @param RelationsInterface $relations
     * @param DefinitionInterface $definitions
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
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * Returns relations
     *
     * @return RelationsInterface
     */
    public function getRelations()
    {
        return $this->relations;
    }
}
