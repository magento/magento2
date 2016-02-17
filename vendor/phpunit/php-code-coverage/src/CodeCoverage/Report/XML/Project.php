<?php
/*
 * This file is part of the PHP_CodeCoverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @since Class available since Release 2.0.0
 */
class PHP_CodeCoverage_Report_XML_Project extends PHP_CodeCoverage_Report_XML_Node
{
    public function __construct($name)
    {
        $this->init();
        $this->setProjectName($name);
    }

    private function init()
    {
        $dom = new DOMDocument;
        $dom->loadXML('<?xml version="1.0" ?><phpunit xmlns="http://schema.phpunit.de/coverage/1.0"><project/></phpunit>');

        $this->setContextNode(
            $dom->getElementsByTagNameNS(
                'http://schema.phpunit.de/coverage/1.0',
                'project'
            )->item(0)
        );
    }

    private function setProjectName($name)
    {
        $this->getContextNode()->setAttribute('name', $name);
    }

    public function getTests()
    {
        $testsNode = $this->getContextNode()->getElementsByTagNameNS(
            'http://schema.phpunit.de/coverage/1.0',
            'tests'
        )->item(0);

        if (!$testsNode) {
            $testsNode = $this->getContextNode()->appendChild(
                $this->getDom()->createElementNS(
                    'http://schema.phpunit.de/coverage/1.0',
                    'tests'
                )
            );
        }

        return new PHP_CodeCoverage_Report_XML_Tests($testsNode);
    }

    public function asDom()
    {
        return $this->getDom();
    }
}
