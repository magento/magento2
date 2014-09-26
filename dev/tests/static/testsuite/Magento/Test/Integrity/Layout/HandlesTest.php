<?php
/**
 * Test format of layout files
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
namespace Magento\Test\Integrity\Layout;

class HandlesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testHandleDeclarations()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Test dependencies between handle attributes that is out of coverage by XSD
             *
             * @param string $layoutFile
             */
            function ($layoutFile) {
                $issues = array();
                $node = simplexml_load_file($layoutFile);
                $label = $node['label'];
                $designAbstraction = $node['design_abstraction'];
                if (!$label) {
                    if ($designAbstraction) {
                        $issues[] = 'Attribute "design_abstraction" is defined, but "label" is not';
                    }
                }

                if ($issues) {
                    $this->fail("Issues found in handle declaration:\n" . implode("\n", $issues) . "\n");
                }
            },
            \Magento\TestFramework\Utility\Files::init()->getLayoutFiles()
        );
    }

    public function testContainerDeclarations()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Test dependencies between container attributes that is out of coverage by XSD
             *
             * @param string $layoutFile
             */
            function ($layoutFile) {
                $issues = array();
                $xml = simplexml_load_file($layoutFile);
                $containers = $xml->xpath('/layout//container') ?: array();
                /** @var \SimpleXMLElement $node */
                foreach ($containers as $node) {
                    if (!isset($node['htmlTag']) && (isset($node['htmlId']) || isset($node['htmlClass']))) {
                        $issues[] = $node->asXML();
                    }
                }
                if ($issues) {
                    $this->fail(
                        'The following containers declare attribute "htmlId" and/or "htmlClass", but not "htmlTag":' .
                        "\n" .
                        implode(
                            "\n",
                            $issues
                        ) . "\n"
                    );
                }
            },
            \Magento\TestFramework\Utility\Files::init()->getLayoutFiles()
        );
    }

    public function testHeadBlockUsage()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
        /**
         * Test validate that head block doesn't exist in layout
         *
         * @param string $layoutFile
         */
            function ($layoutFile) {
                $dom = new \DOMDocument();
                $dom->load($layoutFile);
                $xpath = new \DOMXpath($dom);
                if ($xpath->query("//*[@name='head']")->length) {
                    $this->fail('Following file contains deprecated head block. File Path:' . "\n" . $layoutFile);
                }
            },
            \Magento\TestFramework\Utility\Files::init()->getLayoutFiles()
        );
    }
}
