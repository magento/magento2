<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\XmlCatalog\Format\PhpStorm;

use DOMDocument;

class DomDocumentFactory
{
    /**
     * @var \Magento\Framework\DomDocument\DomDocumentFactory
     */
    private $documentFactory;

    /**
     * DomDocumentFactory constructor.
     * @param \Magento\Framework\DomDocument\DomDocumentFactory $documentFactory
     */
    public function __construct(\Magento\Framework\DomDocument\DomDocumentFactory $documentFactory)
    {
        $this->documentFactory = $documentFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data = null)
    {
        $dom = $this->documentFactory->create($data);

        if (empty($data)) {
            $this->initializeDocument($dom);
        }

        return $dom;
    }

    /**
     * Initialize document to be used as 'misc.xml'
     *
     * @param DOMDocument $document
     * @return DOMDocument
     */
    private function initializeDocument(DOMDocument $document)
    {
        $document->xmlVersion = '1.0';
        $projectNode = $document->createElement('project');

        //PhpStorm 9 version for component is "4"
        $projectNode->setAttribute('version', '4');
        $document->appendChild($projectNode);
        $rootComponentNode = $document->createElement('component');

        //PhpStorm 9 version for ProjectRootManager is "2"
        $rootComponentNode->setAttribute('version', '2');
        $rootComponentNode->setAttribute('name', 'ProjectRootManager');
        $projectNode->appendChild($rootComponentNode);

        return $document;
    }
}
