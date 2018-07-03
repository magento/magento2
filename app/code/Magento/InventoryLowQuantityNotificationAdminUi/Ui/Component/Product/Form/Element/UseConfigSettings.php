<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotificationAdminUi\Ui\Component\Product\Form\Element;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\ValueSourceInterface;
use Magento\Framework\Serialize\JsonValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Checkbox;

/**
 * Class UseConfigSettings sets default value from configuration.
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
     * @param ContextInterface $context
     * @param Json $serializer
     * @param JsonValidator $jsonValidator
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        Json $serializer,
        JsonValidator $jsonValidator,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);

        $this->serializer = $serializer;
        $this->jsonValidator = $jsonValidator;
    }

    /**
     * Prepare component configuration.
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
