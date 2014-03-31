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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integrity_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\PageType;

class FilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string - xsd that will validate page_types.xml against
     */
    protected $_schemaFile;

    protected function setUp()
    {
        $this->_schemaFile = \Magento\TestFramework\Utility\Files::init()->getModuleFile(
            'Magento',
            'Core',
            'etc/page_types.xsd'
        );
    }

    public function testPageType()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            function ($pageType) {
                $dom = new \DOMDocument();
                $dom->loadXML(file_get_contents($pageType));
                $errors = \Magento\TestFramework\Utility\Validator::validateXml($dom, $this->_schemaFile);
                $this->assertTrue(empty($errors), print_r($errors, true));
            },
            \Magento\TestFramework\Utility\Files::init()->getPageTypeFiles()
        );
    }
}
