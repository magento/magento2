<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\DataProvider\Config;

use Magento\Framework\Config\ConverterInterface;

/**
 * Class Converter
 */
class Converter implements ConverterInterface
{
    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory
     */
    protected $entityTypeFactory;

    /**
     * Map EAV frontend_input property to form element types
     *
     * @var array
     */
    protected $inputTypeMap = [
        'text' => 'input',
        'textarea' => 'textarea',
        'multiline' => 'input',
        'date' => 'date',
        'select' => 'select',
        'multiselect' => 'multiselect',
        'boolean' => 'select',
        'file' => 'media',
        'image' => 'media',
    ];

    /**
     * @param \Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory
     */
    public function __construct(\Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory)
    {
        $this->entityTypeFactory = $entityTypeFactory;
    }

    /**
     * Transform Xml to array
     *
     * @param \DOMNode $source
     * @return array
     */
    protected function toArray(\DOMNode $source)
    {
        $result = [];
        if ($source->hasAttributes()) {
            foreach ($source->attributes as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if (!$source->hasChildNodes()) {
            if (empty($result)) {
                $result = $source->nodeValue;
            }
        } else {
            if ($source->hasChildNodes()) {
                $groups = [];
                foreach ($source->childNodes as $child) {
                    if ($child->nodeType == XML_TEXT_NODE || $child->nodeType == XML_COMMENT_NODE) {
                        continue;
                    }
                    if ($this->isTextNode($child)) {
                        $result[$child->nodeName] = $this->getTextNode($child)->data;
                    } else {
                        if (in_array($child->nodeName, ['validate', 'filter', 'readonly'])) {
                            if (!isset($result[$child->nodeName])) {
                                $result[$child->nodeName] = [];
                            }
                            $result[$child->nodeName][] = $this->toArray($child);
                        } else {
                            if (isset($result[$child->nodeName])) {
                                if (!isset($groups[$child->nodeName])) {
                                    $result[$child->nodeName] = [$result[$child->nodeName]];
                                    $groups[$child->nodeName] = 1;
                                }
                                $result[$child->nodeName][] = $this->toArray($child);
                            } else {
                                $result[$child->nodeName] = $this->toArray($child);
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Convert configuration
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $data = [];
        $output = $this->toArray($source);
        foreach ($output['config']['dataSource'] as $dataSource) {
            $data[$dataSource['@attributes']['name']] = [
                'name' => $dataSource['@attributes']['name'],
                'label' => $dataSource['@attributes']['label'],
                'dataSet' => $dataSource['@attributes']['dataSet'],
            ];
            $fields = [];
            if (isset($dataSource['fields']['@attributes']['entityType'])) {
                $entityType = $this->entityTypeFactory->create()
                    ->load($dataSource['fields']['@attributes']['entityType'], 'entity_type_code');
                $attributeCollection = $entityType->getAttributeCollection();
                foreach ($attributeCollection as $attribute) {
                    if ($attribute->getIsUserDefined()) {
                        $fields[$attribute->getAttributeCode()] = [
                            'name' => $attribute->getAttributeCode(),
                            'source' => 'eav',
                            'formElement' => $this->mapFrontendInput($attribute->getFrontendInput()),
                            'is_required' => $attribute->getScopeIsRequired(),
                            'default_value' => $attribute->getScopeDefaultValue(),
                            'visible' => $attribute->getScopeIsVisible(),
                            'multiline_count' => $attribute->getScopeMultilineCount(),
                        ];
                        if ($attribute->getValidateRules()) {
                            $fields[$attribute->getAttributeCode()]['constraints']['validate']
                                = $attribute->getValidateRules();
                        }
                    }
                }
            }
            foreach ($dataSource['fields']['field'] as $field) {
                foreach ($field['@attributes'] as $key => $value) {
                    $fields[$field['@attributes']['name']][$key] = $value;
                }
                if (isset($field['@attributes']['source'])) {
                    if (in_array($field['@attributes']['source'], ['lookup', 'option', 'reference'])) {
                        $fields[$field['@attributes']['name']]['reference'] = [
                            'target' => $field['reference']['@attributes']['target'],
                            'targetField' => $field['reference']['@attributes']['targetField'],
                            'referencedField' => $field['reference']['@attributes']['referencedField'],
                            'neededField' => $field['reference']['@attributes']['neededField'],
                        ];
                    }
                }
                if (isset($field['tooltip'])) {
                    $fields[$field['@attributes']['name']]['tooltip'] = [
                        'link' => $field['tooltip']['link'],
                        'description' => $field['tooltip']['description'],
                    ];
                }
                if (isset($field['constraints']['validate'])) {
                    foreach ($field['constraints']['validate'] as $rule) {
                        $fields[$field['@attributes']['name']]['constraints']['validate'][$rule['@attributes']['name']] =
                            isset($rule['@attribute']['value'])
                                ? $rule['@attribute']['value'] : true;
                    }
                }
                if (isset($field['constraints']['filter'])) {
                    foreach ($field['constraints']['filter'] as $filter) {
                        $filterValues['on'] = isset($filter['@attributes']['on']) ? $filter['@attributes']['on'] : null;
                        $filterValues['by'] = isset($filter['@attributes']['by']) ? $filter['@attributes']['by'] : null;
                        $filterValues['value'] = isset($filter['@attributes']['value'])
                            ? $filter['@attributes']['value'] : null;
                        $fields[$field['@attributes']['name']]['constraints']['filter'][] = $filterValues;
                    }
                }
                if (isset($field['constraints']['readonly'])) {
                    foreach ($field['constraints']['readonly'] as $condition) {
                        $fields[$field['@attributes']['name']]['constraints']['readonly'][] = [
                            'on' => $condition['@attributes']['on'],
                            'value' => $condition['@attributes']['value'],
                        ];
                    }
                }
            }
            $data[$dataSource['@attributes']['name']]['fields'] = $fields;
            if (!empty($dataSource['references'])) {
                foreach ($dataSource['references'] as $reference) {
                    $data[$reference['@attributes']['target']]['children'][$dataSource['@attributes']['name']][] = [
                        'targetField' => $reference['@attributes']['targetField'],
                        'referencedField' => $reference['@attributes']['referencedField'],
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    protected function isTextNode(\DOMNode $node)
    {
        $result = true;
        if (!$node instanceof \DOMText) {
            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $child) {
                    if ($child->nodeType != XML_TEXT_NODE) {
                        $result = false;
                        break;
                    }
                }
            } else {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param \DOMNode $node
     * @return \DOMText
     */
    protected function getTextNode(\DOMNode $node)
    {
        if ($node instanceof \DOMText) {
            return $node;
        }
        foreach ($node->childNodes as $child) {
            if ($child->nodeType == XML_TEXT_NODE) {
                return $child;
            }
        }
        return false;
    }

    /**
     * @param string $input
     * @return string
     */
    protected function mapFrontendInput($input)
    {
        return isset($this->inputTypeMap[$input]) ? $this->inputTypeMap[$input] : 'input';
    }
}
