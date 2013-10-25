<?php
/**
 * In-memory pool of all template engines available in the system
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\TemplateEngine;

class Pool
{
    /**
     * @var Factory
     */
    protected $_factory;

    /**
     * @var EngineInterface[]
     */
    protected $_engines = array();

    /**
     * @param Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * Retrieve a template engine instance by its unique name
     *
     * @param string $name
     * @return EngineInterface
     */
    public function get($name)
    {
        if (!isset($this->_engines[$name])) {
            $this->_engines[$name] = $this->_factory->create($name);
        }
        return $this->_engines[$name];
    }
}
