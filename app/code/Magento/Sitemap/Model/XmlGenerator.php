<?php

namespace Magento\Sitemap\Model;


use Magento\Framework\Xml\Generator;
use Magento\Sitemap\Api\Data\SitemapInterface;
use Magento\Sitemap\Api\XmlGeneratorInterface;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;

class XmlGenerator implements XmlGeneratorInterface
{
    /**
     * @var ItemProviderInterface
     */
    private $itemProvider;
    /**
     * @var SitemapItemInterfaceFactory
     */
    private $sitemapItemFactory;
    /**
     * @var Generator
     */
    private $generator;

    /**
     * XmlGenerator constructor.
     * @param ItemProviderInterface $itemProvider
     * @param SitemapItemInterfaceFactory $sitemapItemFactory
     * @param Generator $generator
     */
    public function __construct(
        ItemProviderInterface $itemProvider,
        SitemapItemInterfaceFactory $sitemapItemFactory,
        Generator $generator
    ) {

        $this->itemProvider = $itemProvider;
        $this->sitemapItemFactory = $sitemapItemFactory;
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(SitemapInterface $sitemap): bool
    {
        $sitemapOutput = '';
        $mapItems = [];
        $this->generator->setIndexedArrayItemName('url');
        /** @var SitemapItemInterface[] $items */
        $items = $this->itemProvider->getItems($sitemap->getStoreId());
        foreach($items as $item) {
            $mapItems[] = $item->toArray();
        }

        $sitemapOutput = $this->generator->arrayToXml($mapItems);
            echo $sitemapOutput . PHP_EOL;


        return true;
    }


}