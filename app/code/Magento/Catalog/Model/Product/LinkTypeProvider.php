<?php
/**
 * Collection of the available product link types
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

class LinkTypeProvider implements \Magento\Catalog\Api\ProductLinkTypeListInterface
{
    /**
     * Available product link types
     *
     * Represented by an assoc array with the following format 'product_link_name' => 'product_link_code'
     *
     * @var array
     */
    protected $linkTypes;

    /**
     * @var \Magento\Catalog\Api\Data\ProductLinkTypeDataBuilder
     */
    protected $linkTypeBuilder;

    /**
     * @var \Magento\Catalog\Api\Data\ProductLinkAttributeDataBuilder
     */
    protected $linkAttributeBuilder;

    /**
     * @var \Magento\Catalog\Model\Product\LinkFactory
     */
    protected $linkFactory;

    /**
     * @param \Magento\Catalog\Api\Data\ProductLinkTypeDataBuilder $linkTypeBuilder
     * @param \Magento\Catalog\Api\Data\ProductLinkAttributeDataBuilder $linkAttributeBuilder
     * @param LinkFactory $linkFactory
     * @param array $linkTypes
     */
    public function __construct(
        \Magento\Catalog\Api\Data\ProductLinkTypeDataBuilder $linkTypeBuilder,
        \Magento\Catalog\Api\Data\ProductLinkAttributeDataBuilder $linkAttributeBuilder,
        \Magento\Catalog\Model\Product\LinkFactory $linkFactory,
        array $linkTypes = []
    ) {
        $this->linkTypes = $linkTypes;
        $this->linkTypeBuilder = $linkTypeBuilder;
        $this->linkAttributeBuilder = $linkAttributeBuilder;
        $this->linkFactory = $linkFactory;
    }

    /**
     * Retrieve information about available product link types
     *
     * @return array
     */
    public function getLinkTypes()
    {
        return $this->linkTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $output = [];
        foreach ($this->getLinkTypes() as $type => $typeCode) {
            $output[] = $this->linkTypeBuilder
                ->populateWithArray(['name' => $type, 'code' => $typeCode])
                ->create();
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemAttributes($type)
    {
        $output = [];
        $types = $this->getLinkTypes();
        $typeId = isset($types[$type]) ? $types[$type] : null;

        /** @var \Magento\Catalog\Model\Product\Link $link */
        $link = $this->linkFactory->create(['data' => ['link_type_id' => $typeId]]);
        $attributes = $link->getAttributes();
        foreach ($attributes as $item) {
            $data = ['code' => $item['code'], 'type' => $item['type']];
            $output[] = $this->linkAttributeBuilder->populateWithArray($data)->create();
        }
        return $output;
    }
}
