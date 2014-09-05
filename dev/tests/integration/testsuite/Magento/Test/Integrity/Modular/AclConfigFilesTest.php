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
namespace Magento\Test\Integrity\Modular;

class AclConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Configuration acl file list
     *
     * @var array
     */
    protected $_fileList = array();

    /**
     * Path to scheme file
     *
     * @var string
     */
    protected $_schemeFile;

    protected function setUp()
    {
        $this->_schemeFile = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Filesystem'
        )->getPath(
            \Magento\Framework\App\Filesystem::LIB_INTERNAL
        ) . '/Magento/Framework/Acl/etc/acl.xsd';
    }

    /**
     * Test each acl configuration file
     * @param string $file
     * @dataProvider aclConfigFileDataProvider
     */
    public function testAclConfigFile($file)
    {
        $domConfig = new \Magento\Framework\Config\Dom(file_get_contents($file));
        $result = $domConfig->validate($this->_schemeFile, $errors);
        $message = "Invalid XML-file: {$file}\n";
        foreach ($errors as $error) {
            $message .= "{$error}\n";
        }
        $this->assertTrue($result, $message);
    }

    /**
     * @return array
     */
    public function aclConfigFileDataProvider()
    {
        return \Magento\TestFramework\Utility\Files::init()->getConfigFiles('acl.xml');
    }
}
