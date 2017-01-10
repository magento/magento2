<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convert($source)
    {
        $widgets = [];
        $xpath = new \DOMXPath($source);
        /** @var $widget \DOMNode */
        foreach ($xpath->query('/widgets/widget') as $widget) {
            $widgetAttributes = $widget->attributes;
            $widgetArray = ['@' => []];
            $widgetArray['@']['type'] = $widgetAttributes->getNamedItem('class')->nodeValue;

            $isEmailCompatible = $widgetAttributes->getNamedItem('is_email_compatible');
            if ($isEmailCompatible !== null) {
                $widgetArray['is_email_compatible'] = $isEmailCompatible->nodeValue == 'true' ? '1' : '0';
            }
            $placeholderImage = $widgetAttributes->getNamedItem('placeholder_image');
            if ($placeholderImage !== null) {
                $widgetArray['placeholder_image'] = $placeholderImage->nodeValue;
            }

            $widgetId = $widgetAttributes->getNamedItem('id');
            /** @var $widgetSubNode \DOMNode */
            foreach ($widget->childNodes as $widgetSubNode) {
                switch ($widgetSubNode->nodeName) {
                    case 'label':
                        $widgetArray['name'] = $widgetSubNode->nodeValue;
                        break;
                    case 'description':
                        $widgetArray['description'] = $widgetSubNode->nodeValue;
                        break;
                    case 'parameters':
                        /** @var $parameter \DOMNode */
                        foreach ($widgetSubNode->childNodes as $parameter) {
                            if ($parameter->nodeName === '#text') {
                                continue;
                            }
                            $subNodeAttributes = $parameter->attributes;
                            $parameterName = $subNodeAttributes->getNamedItem('name')->nodeValue;
                            $widgetArray['parameters'][$parameterName] = $this->_convertParameter($parameter);
                        }
                        break;
                    case 'containers':
                        if (!isset($widgetArray['supported_containers'])) {
                            $widgetArray['supported_containers'] = [];
                        }
                        foreach ($widgetSubNode->childNodes as $container) {
                            if ($container->nodeName === '#text') {
                                continue;
                            }
                            $widgetArray['supported_containers'] = array_merge(
                                $widgetArray['supported_containers'],
                                $this->_convertContainer($container)
                            );
                        }
                        break;
                    case "#text":
                        break;
                    case '#comment':
                        break;
                    default:
                        throw new \LogicException(
                            sprintf(
                                "Unsupported child xml node '%s' found in the 'widget' node",
                                $widgetSubNode->nodeName
                            )
                        );
                }
            }
            $widgets[$widgetId->nodeValue] = $widgetArray;
        }
        return $widgets;
    }

    /**
     * Convert dom Container node to Magento array
     *
     * @param \DOMNode $source
     * @return array
     * @throws \LogicException
     */
    protected function _convertContainer($source)
    {
        $supportedContainers = [];
        $containerAttributes = $source->attributes;
        $template = [];
        foreach ($source->childNodes as $containerTemplate) {
            if (!$containerTemplate instanceof \DOMElement) {
                continue;
            }
            if ($containerTemplate->nodeName !== 'template') {
                throw new \LogicException("Only 'template' node can be child of 'container' node");
            }
            $templateAttributes = $containerTemplate->attributes;
            $template[$templateAttributes->getNamedItem(
                'name'
            )->nodeValue] = $templateAttributes->getNamedItem(
                'value'
            )->nodeValue;
        }
        $supportedContainers[] = [
            'container_name' => $containerAttributes->getNamedItem('name')->nodeValue,
            'template' => $template,
        ];
        return $supportedContainers;
    }

    /**
     * Convert dom Parameter node to Magento array
     *
     * @param \DOMNode $source
     * @return array
     * @throws \LogicException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _convertParameter($source)
    {
        $parameter = [];
        $sourceAttributes = $source->attributes;
        $xsiType = $sourceAttributes->getNamedItem('type')->nodeValue;
        if ($xsiType == 'block') {
            $parameter['type'] = 'label';
            $parameter['@'] = [];
            $parameter['@']['type'] = 'complex';
            foreach ($source->childNodes as $blockSubNode) {
                if ($blockSubNode->nodeName == 'block') {
                    $parameter['helper_block'] = $this->_convertBlock($blockSubNode);
                    break;
                }
            }
        } elseif ($xsiType == 'select' || $xsiType == 'multiselect') {
            $sourceModel = $sourceAttributes->getNamedItem('source_model');
            if ($sourceModel !== null) {
                $parameter['source_model'] = $sourceModel->nodeValue;
            }
            $parameter['type'] = $xsiType;

            /** @var $paramSubNode \DOMNode */
            foreach ($source->childNodes as $paramSubNode) {
                if ($paramSubNode->nodeName == 'options') {
                    /** @var $option \DOMNode */
                    foreach ($paramSubNode->childNodes as $option) {
                        if ($option->nodeName === '#text') {
                            continue;
                        }
                        $optionAttributes = $option->attributes;
                        $optionName = $optionAttributes->getNamedItem('name')->nodeValue;
                        $selected = $optionAttributes->getNamedItem('selected');
                        if ($selected !== null) {
                            $parameter['value'] = $optionAttributes->getNamedItem('value')->nodeValue;
                        }
                        if (!isset($parameter['values'])) {
                            $parameter['values'] = [];
                        }
                        $parameter['values'][$optionName] = $this->_convertOption($option);
                    }
                }
            }
        } elseif ($xsiType == 'text') {
            $parameter['type'] = $xsiType;
            foreach ($source->childNodes as $textSubNode) {
                if ($textSubNode->nodeName == 'value') {
                    $parameter['value'] = $textSubNode->nodeValue;
                }
            }
        } elseif ($xsiType == 'conditions') {
            $parameter['type'] = $sourceAttributes->getNamedItem('class')->nodeValue;
        } else {
            $parameter['type'] = $xsiType;
        }
        $visible = $sourceAttributes->getNamedItem('visible');
        if ($visible) {
            $parameter['visible'] = $visible->nodeValue == 'true' ? '1' : '0';
        } else {
            $parameter['visible'] = true;
        }
        $required = $sourceAttributes->getNamedItem('required');
        if ($required) {
            $parameter['required'] = $required->nodeValue == 'false' ? '0' : '1';
        }
        $sortOrder = $sourceAttributes->getNamedItem('sort_order');
        if ($sortOrder) {
            $parameter['sort_order'] = $sortOrder->nodeValue;
        }
        foreach ($source->childNodes as $paramSubNode) {
            switch ($paramSubNode->nodeName) {
                case 'label':
                    $parameter['label'] = $paramSubNode->nodeValue;
                    break;
                case 'description':
                    $parameter['description'] = $paramSubNode->nodeValue;
                    break;
                case 'depends':
                    $parameter['depends'] = $this->_convertDepends($paramSubNode);
                    break;
            }
        }
        return $parameter;
    }

    /**
     * Convert dom Depends node to Magento array
     *
     * @param \DOMNode $source
     * @return array
     * @throws \LogicException
     */
    protected function _convertDepends($source)
    {
        $depends = [];
        foreach ($source->childNodes as $childNode) {
            if ($childNode->nodeName == '#text') {
                continue;
            }
            if ($childNode->nodeName !== 'parameter') {
                throw new \LogicException(
                    sprintf("Only 'parameter' node can be child of 'depends' node, %s found", $childNode->nodeName)
                );
            }
            $parameterAttributes = $childNode->attributes;
            $depends[$parameterAttributes->getNamedItem(
                'name'
            )->nodeValue] = [
                'value' => $parameterAttributes->getNamedItem('value')->nodeValue,
            ];
        }
        return $depends;
    }

    /**
     * Convert dom Renderer node to Magento array
     *
     * @param \DOMNode $source
     * @return array
     * @throws \LogicException
     */
    protected function _convertBlock($source)
    {
        $helperBlock = [];
        $helperBlock['type'] = $source->attributes->getNamedItem('class')->nodeValue;
        foreach ($source->childNodes as $blockSubNode) {
            if ($blockSubNode->nodeName == '#text') {
                continue;
            }
            if ($blockSubNode->nodeName !== 'data') {
                throw new \LogicException(
                    sprintf("Only 'data' node can be child of 'block' node, %s found", $blockSubNode->nodeName)
                );
            }
            $helperBlock['data'] = $this->_convertData($blockSubNode);
        }
        return $helperBlock;
    }

    /**
     * Convert dom Data node to Magento array
     *
     * @param \DOMElement $source
     * @return array
     */
    protected function _convertData($source)
    {
        $data = [];
        if (!$source->hasChildNodes()) {
            return $data;
        }
        foreach ($source->childNodes as $dataChild) {
            if ($dataChild instanceof \DOMElement) {
                $data[$dataChild->attributes->getNamedItem('name')->nodeValue] = $this->_convertData($dataChild);
            } else {
                if (strlen(trim($dataChild->nodeValue))) {
                    $data = $dataChild->nodeValue;
                }
            }
        }
        return $data;
    }

    /**
     * Convert dom Option node to Magento array
     *
     * @param \DOMNode $source
     * @return array
     * @throws \LogicException
     */
    protected function _convertOption($source)
    {
        $option = [];
        $optionAttributes = $source->attributes;
        $option['value'] = $optionAttributes->getNamedItem('value')->nodeValue;
        foreach ($source->childNodes as $childNode) {
            if ($childNode->nodeName == '#text') {
                continue;
            }
            if ($childNode->nodeName !== 'label') {
                throw new \LogicException("Only 'label' node can be child of 'option' node");
            }
            $option['label'] = $childNode->nodeValue;
        }
        return $option;
    }
}
