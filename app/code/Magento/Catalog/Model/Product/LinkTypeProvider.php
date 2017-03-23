<?php
/**
 * Collection of the available product link types
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Magento\Catalog\Api\Data\ProductLinkTypeInterfaceFactory
     */
    protected $linkTypeFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductLinkAttributeInterfaceFactory
     */
    protected $linkAttributeFactory;

    /**
     * @var \Magento\Catalog\Model\Product\LinkFactory
     */
    protected $linkFactory;

    /**
     * @param \Magento\Catalog\Api\Data\ProductLinkTypeInterfaceFactory $linkTypeFactory
     * @param \Magento\Catalog\Api\Data\ProductLinkAttributeInterfaceFactory $linkAttributeFactory
     * @param LinkFactory $linkFactory
     * @param array $linkTypes
     */
    public function __construct(
        \Magento\Catalog\Api\Data\ProductLinkTypeInterfaceFactory $linkTypeFactory,
        \Magento\Catalog\Api\Data\ProductLinkAttributeInterfaceFactory $linkAttributeFactory,
        \Magento\Catalog\Model\Product\LinkFactory $linkFactory,
        array $linkTypes = []
    ) {
        $this->linkTypes = $linkTypes;
        $this->linkTypeFactory = $linkTypeFactory;
        $this->linkAttributeFactory = $linkAttributeFactory;
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
            /** @var \Magento\Catalog\Api\Data\ProductLinkTypeInterface $linkType */
            $linkType = $this->linkTypeFactory->create();
            $linkType->setName($type)
                ->setCode($typeCode);
            $output[] = $linkType;
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
            /** @var \Magento\Catalog\Api\Data\ProductLinkAttributeInterface $linkAttribute */
            $linkAttribute = $this->linkAttributeFactory->create();
            $linkAttribute->setCode($item['code'])
                ->setType($item['type']);
            $output[] = $linkAttribute;
        }
        return $output;
    }
}
