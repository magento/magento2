<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml;

use Magento\Framework\View\Layout\Generator\Structure;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Template;
use Magento\Framework\View\TemplateEngine\Xhtml\ResultInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Result
 */
class Result implements ResultInterface
{
    /**
     * @var Template
     */
    protected $template;

    /**
     * @var CompilerInterface
     */
    protected $compiler;

    /**
     * @var UiComponentInterface
     */
    protected $component;

    /**
     * @var Structure
     */
    protected $structure;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Template $template
     * @param CompilerInterface $compiler
     * @param UiComponentInterface $component
     * @param Structure $structure
     * @param LoggerInterface $logger
     */
    public function __construct(
        Template $template,
        CompilerInterface $compiler,
        UiComponentInterface $component,
        Structure $structure,
        LoggerInterface $logger
    ) {
        $this->template = $template;
        $this->compiler = $compiler;
        $this->component = $component;
        $this->structure = $structure;
        $this->logger = $logger;
    }

    /**
     * Get result document root element \DOMElement
     *
     * @return \DOMElement
     */
    public function getDocumentElement()
    {
        return $this->template->getDocumentElement();
    }

    /**
     * Append layout configuration
     *
     * @return void
     */
    public function appendLayoutConfiguration()
    {
        $layoutConfiguration = $this->wrapContent(json_encode($this->structure->generate($this->component)));
        //$formDataSource='"testform_form_data_source":{"type":"dataSource","name":"testform_form_data_source","dataScope":"testform_form","config":{"params":{"namespace":"testform_form"}}}';
        $formDataSource2 = '"myyyyy_data_source":{"type":"dataSource","name":"myyyyy_data_source","dataScope":"testform_form","config":{"params":{"namespace":"testform_form"}}}';
        $listingDataSource = '"listing_data_source":{"type":"dataSource","name":"listing_data_source","dataScope":"testform_form","config":{"update_url":"mui/index/render","component":"Magento_Ui\/js\/grid\/provider","storageConfig":{"indexField":"koala_id"},"params":{"namespace":"testform_form"},"data":{"items":[{"koala_id":1,"name":"Red Koala","id_field_name":"koala_id","positionFromGridSource":"111","positionFromFormSource":"999"}],"totalRecords":1}}}';
        $layoutConfiguration = str_replace("}}}}}}]]></script>", ",".$formDataSource2."}}}}}}]]></script>", $layoutConfiguration);
        $layoutConfiguration = str_replace("}}}}}}]]></script>", ",".$listingDataSource."}}}}}}]]></script>", $layoutConfiguration);
        $this->template->append($layoutConfiguration);
    }

    /**
     * Returns the string representation
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $templateRootElement = $this->getDocumentElement();
            foreach ($templateRootElement->attributes as $name => $attribute) {
                if ('noNamespaceSchemaLocation' === $name) {
                    $this->getDocumentElement()->removeAttributeNode($attribute);
                    break;
                }
            }
            $templateRootElement->removeAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi');
            $this->compiler->compile($templateRootElement, $this->component, $this->component);
            $this->appendLayoutConfiguration();
            $result = $this->compiler->postprocessing($this->template->__toString());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result = $e->getMessage();
        }
        return $result;
    }

    /**
     * Wrap content
     *
     * @param string $content
     * @return string
     */
    protected function wrapContent($content)
    {
        return '<script type="text/x-magento-init"><![CDATA['
        . '{"*": {"Magento_Ui/js/core/app": ' . str_replace(['<![CDATA[', ']]>'], '', $content) . '}}'
        . ']]></script>';
    }
}
