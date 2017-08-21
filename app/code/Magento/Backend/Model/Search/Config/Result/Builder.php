<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Search\Config\Result;

use Magento\Backend\Model\Search\Config\Structure\ElementBuilderInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Config\Model\Config\Structure\ElementNewInterface;

/**
 * Config SearchResult Builder
 */
class Builder
{
    const STRUCTURE_ELEMENT_TYPE_SECTION = 'section';
    const STRUCTURE_ELEMENT_TYPE_GROUP   = 'group';
    const STRUCTURE_ELEMENT_TYPE_FIELD   = 'field';

    /**
     * @var array
     */
    private $results = [];

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ElementBuilderInterface[]
     */
    private $structureElementTypes;

    /**
     * @param UrlInterface $urlBuilder
     * @param array $structureElementTypes
     */
    public function __construct(UrlInterface $urlBuilder, array $structureElementTypes)
    {
        $this->urlBuilder = $urlBuilder;
        $this->structureElementTypes = $structureElementTypes;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->results;
    }

    /**
     * @param ElementNewInterface $structureElement
     * @param string $elementPathLabel
     * @return void
     */
    public function add(ElementNewInterface $structureElement, $elementPathLabel)
    {
        $urlParams = [];
        $elementData = $structureElement->getData();

        if (!in_array($elementData['_elementType'], array_keys($this->structureElementTypes))) {
            return;
        }

        if (isset($this->structureElementTypes[$elementData['_elementType']])) {
            $urlParamsBuilder = $this->structureElementTypes[$elementData['_elementType']];
            $urlParams = $urlParamsBuilder->build($structureElement);
        }

        $this->results[] = [
            'id'          => md5($structureElement->getPath()),
            'type'        => null,
            'name'        => $structureElement->getLabel(),
            'description' => $elementPathLabel,
            'url'         => $this->urlBuilder->getUrl('*/system_config/edit', $urlParams),
        ];
    }
}
