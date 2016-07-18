<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Theme\Api\Data\DesignConfigInterface;
use \Magento\Theme\Api\Data\DesignConfigDataInterface;
use \Magento\Framework\Mail\TemplateInterfaceFactory as TemplateFactory;
use \Magento\Framework\Filter\Template;
use \Magento\Framework\Filter\Template\Tokenizer\Parameter as ParameterTokenizer;

/**
 * Design configuration validator
 */
class Validator
{
    /**
     * @var string[]
     */
    private $fields = [];

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * Initialize dependencies.
     *
     * @param TemplateFactory $templateFactory
     * @param string[] $fields
     */
    public function __construct(TemplateFactory $templateFactory, $fields = [])
    {
        $this->templateFactory = $templateFactory;
        $this->fields = $fields;
    }

    /**
     * Validate if design configuration has recursive references
     *
     * @param DesignConfigInterface $designConfig
     *
     * @throws LocalizedException
     * @return void
     */
    public function validate(DesignConfigInterface $designConfig)
    {
        /** @var DesignConfigDataInterface[] $designConfigData */
        $designConfigData = $designConfig->getExtensionAttributes()->getDesignConfigData();
        $elements = [];
        foreach ($designConfigData as $designElement) {
            if (!in_array($designElement->getFieldConfig()['field'], $this->fields)) {
                continue;
            }
            /* Save mapping between field names and config paths */
            $elements[$designElement->getFieldConfig()['field']] = [
                'config_path' => $designElement->getPath(),
                'value' => $designElement->getValue()
            ];
        }

        foreach ($elements as $name => $data) {
            // Load template object by configured template id
            $template = $this->templateFactory->create();
            $template->emulateDesign($designConfig->getScopeId());
            $templateId = $data['value'];
            if (is_numeric($templateId)) {
                $template->load($templateId);
            } else {
                $template->loadDefault($templateId);
            }
            $text = $template->getTemplateText();
            $template->revertDesign();
            // Check if template body has a reference to the same config path
            if (preg_match_all(Template::CONSTRUCTION_TEMPLATE_PATTERN, $text, $constructions, PREG_SET_ORDER)) {
                foreach ($constructions as $construction) {
                    $configPath = isset($construction[2]) ? $construction[2] : '';
                    $params = $this->getParameters($configPath);
                    if (isset($params['config_path']) && $params['config_path'] == $data['config_path']) {
                        throw new LocalizedException(
                            __(
                                "The %templateName contains an incorrect configuration. The template has " .
                                "a reference to itself. Either remove or change the reference.",
                                ["templateName" => $name]
                            )
                        );
                    };
                }
            }
        }
    }

    /**
     * Return associative array of parameters.
     *
     * @param string $value raw parameters
     * @return array
     */
    private function getParameters($value)
    {
        $tokenizer = new ParameterTokenizer();
        $tokenizer->setString($value);
        $params = $tokenizer->tokenize();
        return $params;
    }
}
