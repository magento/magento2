<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Search\Config\Result;

use Magento\Config\Model\Config\Structure\Element\AbstractComposite;
use Magento\Config\Model\Config\Structure\ElementInterface;

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
    private $supportedElementTypes = [
        self::STRUCTURE_ELEMENT_TYPE_SECTION,
        self::STRUCTURE_ELEMENT_TYPE_GROUP,
        self::STRUCTURE_ELEMENT_TYPE_FIELD,
    ];

    /**
     * @var array
     */
    private $results = [];

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;

    /**
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     */
    public function __construct(\Magento\Backend\Model\UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->results;
    }

    /**
     * @param AbstractComposite|ElementInterface $structureElement
     * @param string $elementPathLabel
     */
    public function add(ElementInterface $structureElement, $elementPathLabel)
    {
        $urlParams = [];
        $elementData = $structureElement->getData();

        if (!in_array($elementData['_elementType'], $this->supportedElementTypes)) {
            return;
        }

        $elementPathParts = explode('/', $structureElement->getPath());

        switch ($elementData['_elementType']) {
            case self::STRUCTURE_ELEMENT_TYPE_SECTION:
                $urlParams = ['section' => $elementPathParts[1]];
                break;
            case self::STRUCTURE_ELEMENT_TYPE_GROUP:
                $urlParams = [
                    'section' => $elementPathParts[0],
                    'group'   => $elementPathParts[1],
                ];
                break;
            case self::STRUCTURE_ELEMENT_TYPE_FIELD:
                $urlParams = [
                    'section' => $elementPathParts[0],
                    'group'   => $elementPathParts[1],
                    'field'   => $structureElement->getId(),
                ];
                break;
        }

        $this->results[] = [
            'id'          => md5($structureElement->getPath()),
            'type'        => __('Config'),
            'name'        => $structureElement->getLabel(),
            'description' => $elementPathLabel,
            'url'         => $this->urlBuilder->getUrl('*/system_config/edit', $urlParams),
        ];
    }
}
