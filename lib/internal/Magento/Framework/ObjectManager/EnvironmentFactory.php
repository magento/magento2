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
namespace Magento\Framework\ObjectManager;

use Magento\Framework\ObjectManager\Environment\Compiled;
use Magento\Framework\ObjectManager\Environment\Developer;

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
        if (file_exists(Compiled::getFilePath())) {
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
