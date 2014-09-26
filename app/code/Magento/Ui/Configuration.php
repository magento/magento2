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
namespace Magento\Ui;

use Magento\Framework\View\Element\UiComponent\ConfigInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigInterface
{
    /**
     * Configuration data
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * Name of owner
     *
     * @var string
     */
    protected $name;

    /**
     * Name of parent owner
     *
     * @var string
     */
    protected $parentName;

    /**
     * Constructor
     *
     * @param string $name
     * @param  string $parentName
     * @param array $configuration
     */
    public function __construct($name, $parentName, $configuration = [])
    {
        $this->name = $name;
        $this->parentName = $parentName;
        $this->configuration = $configuration;
    }

    /**
     * Get configuration data
     *
     * @param string|null $key
     * @return mixed
     */
    public function getData($key = null)
    {
        if ($key === null) {
            return (array)$this->configuration;
        }
        return isset($this->configuration[$key]) ? $this->configuration[$key] : null;
    }

    /**
     * Add configuration data
     *
     * @param string $key
     * @param mixed $data
     * @return mixed
     */
    public function addData($key, $data)
    {
        if (!isset($this->configuration[$key])) {
            $this->configuration[$key] = $data;
        }
    }

    /**
     * Update configuration data
     *
     * @param string $key
     * @param mixed $data
     * @return mixed
     */
    public function updateData($key, $data)
    {
        $this->configuration[$key] = $data;
    }

    /**
     * Get owner name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get owner parent name
     *
     * @return string
     */
    public function getParentName()
    {
        return $this->parentName;
    }
}
