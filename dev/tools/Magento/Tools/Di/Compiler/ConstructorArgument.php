<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tools\Di\Compiler;

class ConstructorArgument
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $isRequired;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->name = $configuration[0];
        $this->type = $configuration[1];
        $this->isRequired = $configuration[2];
        $this->defaultValue = $configuration[3];
    }

    /**
     * Returns attribute name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns attribute type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Whether attribute is required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->isRequired;
    }

    /**
     * Returns attribute default value
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
