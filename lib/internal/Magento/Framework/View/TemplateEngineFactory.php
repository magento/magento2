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
namespace Magento\Framework\View;

use Magento\Framework\ObjectManager;

/**
 * Factory class for Template Engine
 */
class TemplateEngineFactory
{
    /**
     * Object manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Engines
     *
     * @var array
     */
    protected $engines;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     * @param array $engines Format: array('<name>' => 'TemplateEngine\Class', ...)
     */
    public function __construct(ObjectManager $objectManager, array $engines)
    {
        $this->objectManager = $objectManager;
        $this->engines = $engines;
    }

    /**
     * Retrieve a template engine instance by its unique name
     *
     * @param string $name
     * @return TemplateEngineInterface
     * @throws \UnexpectedValueException If template engine doesn't implement the necessary interface
     * @throws \InvalidArgumentException If template engine doesn't exist
     */
    public function create($name)
    {
        if (!isset($this->engines[$name])) {
            throw new \InvalidArgumentException("Unknown template engine type: '{$name}'.");
        }
        $engineClass = $this->engines[$name];
        $engineInstance = $this->objectManager->create($engineClass);
        if (!$engineInstance instanceof \Magento\Framework\View\TemplateEngineInterface) {
            throw new \UnexpectedValueException("{$engineClass} has to implement the template engine interface.");
        }
        return $engineInstance;
    }
}
