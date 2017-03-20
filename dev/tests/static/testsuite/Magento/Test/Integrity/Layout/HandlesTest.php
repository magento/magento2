<?php
/**
 * Test format of layout files
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Layout;

class HandlesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testHandleDeclarations()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Test dependencies between handle attributes that is out of coverage by XSD
             *
             * @param string $layoutFile
             */
            function ($layoutFile) {
                $issues = [];
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
            \Magento\Framework\App\Utility\Files::init()->getLayoutFiles()
        );
    }

    public function testContainerDeclarations()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Test dependencies between container attributes that is out of coverage by XSD
             *
             * @param string $layoutFile
             */
            function ($layoutFile) {
                $issues = [];
                $xml = simplexml_load_file($layoutFile);
                $containers = $xml->xpath('/layout//container') ?: [];
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
            \Magento\Framework\App\Utility\Files::init()->getLayoutFiles()
        );
    }

    public function testHeadBlockUsage()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
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
            \Magento\Framework\App\Utility\Files::init()->getLayoutFiles()
        );
    }
}
