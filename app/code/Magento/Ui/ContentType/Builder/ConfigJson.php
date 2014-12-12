<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\ContentType\Builder;

use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigInterface;

/**
 * Class ConfigJson
 */
class ConfigJson implements ConfigBuilderInterface
{
    /**
     * Config data to JSON by output
     *
     * @param ConfigInterface $configuration
     * @return string
     */
    public function toJson(ConfigInterface $configuration)
    {
        $result = $configuration->getData();
        $result['name'] = $configuration->getName();
        $result['parent_name'] = $configuration->getParentName();

        return json_encode($result);
    }
}
