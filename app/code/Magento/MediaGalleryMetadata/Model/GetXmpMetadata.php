<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterfaceFactory;

/**
 * Get metadata from XMP block
 */
class GetXmpMetadata
{
    private const XMP_XPATH_SELECTOR_TITLE = '//dc:title/rdf:Alt/rdf:li';
    private const XMP_XPATH_SELECTOR_DESCRIPTION = '//dc:description/rdf:Alt/rdf:li';
    private const XMP_XPATH_SELECTOR_KEYWORDS = '//dc:subject/rdf:Bag/rdf:li';

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataFactory;

    /**
     * @param MetadataInterfaceFactory $metadataFactory
     */
    public function __construct(MetadataInterfaceFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * Parse metadata
     *
     * @param string $data
     * @return MetadataInterface
     */
    public function execute(string $data): MetadataInterface
    {
        $xml = simplexml_load_string($data);
        $namespaces = $xml->getNamespaces(true);

        foreach ($namespaces as $prefix => $url) {
            $xml->registerXPathNamespace($prefix, $url);
        }

        $keywords = array_map(
            function (\SimpleXMLElement $element): string {
                return (string) $element;
            },
            $xml->xpath(self::XMP_XPATH_SELECTOR_KEYWORDS)
        );

        $description = implode(' ', $xml->xpath(self::XMP_XPATH_SELECTOR_DESCRIPTION));
        $title = implode(' ', $xml->xpath(self::XMP_XPATH_SELECTOR_TITLE));

        return $this->metadataFactory->create([
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords
        ]);
    }
}
