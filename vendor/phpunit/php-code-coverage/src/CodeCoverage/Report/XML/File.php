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
class PHP_CodeCoverage_Report_XML_File
{
    /**
     * @var DOMDocument
     */
    protected $dom;

    /**
     * @var DOMElement
     */
    protected $contextNode;

    public function __construct(DOMElement $context)
    {
        $this->dom         = $context->ownerDocument;
        $this->contextNode = $context;
    }

    public function getTotals()
    {
        $totalsContainer = $this->contextNode->firstChild;

        if (!$totalsContainer) {
            $totalsContainer = $this->contextNode->appendChild(
                $this->dom->createElementNS(
                    'http://schema.phpunit.de/coverage/1.0',
                    'totals'
                )
            );
        }

        return new PHP_CodeCoverage_Report_XML_Totals($totalsContainer);
    }

    public function getLineCoverage($line)
    {
        $coverage = $this->contextNode->getElementsByTagNameNS(
            'http://schema.phpunit.de/coverage/1.0',
            'coverage'
        )->item(0);

        if (!$coverage) {
            $coverage = $this->contextNode->appendChild(
                $this->dom->createElementNS(
                    'http://schema.phpunit.de/coverage/1.0',
                    'coverage'
                )
            );
        }

        $lineNode = $coverage->appendChild(
            $this->dom->createElementNS(
                'http://schema.phpunit.de/coverage/1.0',
                'line'
            )
        );

        return new PHP_CodeCoverage_Report_XML_File_Coverage($lineNode, $line);
    }
}
