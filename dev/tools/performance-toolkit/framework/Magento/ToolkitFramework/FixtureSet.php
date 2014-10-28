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

namespace Magento\ToolkitFramework;

class FixtureSet
{
    /**
     * Configuration array
     *
     * @var array
     */
    protected $_fixtures = [];

    /**
     * Get config instance
     *
     * @return \Magento\ToolkitFramework\FixtureSet
     */
    public static function getInstance()
    {
        static $instance;
        if (!$instance instanceof static) {
            $instance = new static(__DIR__ . '/../../fixtures.xml');
        }
        return $instance;
    }

    /**
     * Constructor
     *
     * @param string $filename
     * @throws \Exception
     */
    public function __construct($filename)
    {
        if (!is_readable($filename)) {
            throw new \Exception("Fixtures set file `{$filename}` is not readable or does not exists.");
        }
        $this->_fixtures = (new \Magento\Framework\Xml\Parser())->load($filename)->xmlToArray();
    }

    /**
     * Get fixtures array
     *
     * @param array $default
     *
     * @return array
     */
    public function getFixtures($default = array())
    {
        return isset($this->_fixtures['fixtures']) ? $this->_fixtures['fixtures'] : $default;
    }
}
