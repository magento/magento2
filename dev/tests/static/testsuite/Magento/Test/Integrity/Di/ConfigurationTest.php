<?php
/**
 * DI configuration test. Checks configuration of types and virtual types parameters
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

namespace Magento\Test\Integrity\Di;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ObjectManager\Config\Mapper\Dom()
     */
    protected $_mapper;

    protected function setUp()
    {
        $basePath = \Magento\TestFramework\Utility\Files::init()->getPathToSource();
        \Magento\Autoload\IncludePath::addIncludePath(array(
            $basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code',
            $basePath . DIRECTORY_SEPARATOR . 'lib',
        ));
        $this->_mapper = new \Magento\ObjectManager\Config\Mapper\Dom();

    }

    /**
     * @dataProvider configFilesDataProvider
     */
    public function testConfigurationOfInstanceParameters($file)
    {
        $dom = new \DOMDocument();
        $dom->load($file);
        $data = $this->_mapper->convert($dom);

        foreach ($data as $instanceName => $parameters) {
            if (!isset($parameters['parameters']) || empty($parameters['parameters'])) {
                continue;
            }

            if (\Magento\TestFramework\Utility\Classes::isVirtual($instanceName)) {
                $instanceName = \Magento\TestFramework\Utility\Classes::resolveVirtualType($instanceName);
            }
            $parameters = $parameters['parameters'];

            if (!class_exists($instanceName)) {
                $this->fail('Non existed class: ' . $instanceName);
            }

            $reflectionClass = new \ReflectionClass($instanceName);

            $constructor = $reflectionClass->getConstructor();
            if (!$constructor) {
                $this->fail('Class ' . $instanceName . ' does not have __constructor');
            }

            $classParameters = $constructor->getParameters();
            foreach ($classParameters as $classParameter) {
                $parameterName = $classParameter->getName();
                if (array_key_exists($parameterName, $parameters)) {
                    unset($parameters[$parameterName]);
                }
            }
            $this->assertEmpty($parameters,
                'Configuration of ' . $instanceName
                . ' contains data for non-existed parameters: ' . implode(', ', array_keys($parameters))
                . ' in file: ' . $file
            );
        }
    }

    /**
     * @return array
     */
    public function configFilesDataProvider()
    {
        $output = array();
        $files = \Magento\TestFramework\Utility\Files::init()->getDiConfigs();
        foreach ($files as $file) {
            $output[$file] = array($file);
        }
        return $output;
    }
}
