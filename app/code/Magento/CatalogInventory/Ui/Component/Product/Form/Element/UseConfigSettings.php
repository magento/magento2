<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Ui\Component\Product\Form\Element;

use Magento\Framework\Data\ValueSourceInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Checkbox;

/**
 * Class UseConfigSettings sets default value from configuration
 */
class UseConfigSettings extends Checkbox
{
    /** @var Json */
    private $serializer;

    /**
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        ContextInterface $context,
        $components = [],
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct($context, $components, $data);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($config['keyInConfiguration'])
            && isset($config['valueFromConfig'])
            && $config['valueFromConfig'] instanceof ValueSourceInterface
        ) {
            $keyInConfiguration = $config['valueFromConfig']->getValue($config['keyInConfiguration']);
            if (!empty($config['unserialized']) && is_string($keyInConfiguration)) {
                $unserializedValue = $this->serializer->unserialize($keyInConfiguration);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $keyInConfiguration = $unserializedValue;
                }
            }
            $config['valueFromConfig'] = $keyInConfiguration;
        }
        $this->setData('config', (array)$config);

        parent::prepare();
    }
}
