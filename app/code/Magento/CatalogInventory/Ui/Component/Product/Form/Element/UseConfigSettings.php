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
use Magento\Framework\Serialize\JsonValidator;
use Magento\Framework\App\ObjectManager;

/**
 * Class UseConfigSettings sets default value from configuration
 */
class UseConfigSettings extends Checkbox
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var JsonValidator
     */
    private $jsonValidator;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param Json|null $serializer
     * @param JsonValidator|null $jsonValidator
     */
    public function __construct(
        ContextInterface $context,
        $components = [],
        array $data = [],
        Json $serializer = null,
        JsonValidator $jsonValidator = null
    ) {
        parent::__construct($context, $components, $data);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->jsonValidator = $jsonValidator ?: ObjectManager::getInstance()->get(JsonValidator::class);
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
                if ($this->jsonValidator->isValid($keyInConfiguration)) {
                    $keyInConfiguration = $this->serializer->unserialize($keyInConfiguration);
                }
            }
            $config['valueFromConfig'] = $keyInConfiguration;
        }
        $this->setData('config', (array)$config);

        parent::prepare();
    }
}
