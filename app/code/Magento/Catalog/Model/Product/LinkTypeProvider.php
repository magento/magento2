<?php
/**
 * Collection of the available product link types
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
        array $linkTypes = array()
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
