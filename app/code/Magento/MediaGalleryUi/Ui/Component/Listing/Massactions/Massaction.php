<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Ui\Component\Listing\Massactions;

use Magento\Ui\Component\Container;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Massaction comntainer
 */
class Massaction extends Container
{
    private const ACL_IMAGE_ACTIONS = [
        'delete_assets' => 'Magento_MediaGalleryUiApi::delete_assets'
    ];

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param AuthorizationInterface $authorization
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->authorization = $authorization;
    }

    /**
     * @inheritdoc
     */
    public function prepare(): void
    {
        parent::prepare();
        $this->setData(
            'config',
            array_replace_recursive(
                (array)$this->getData('config'),
                [
                    'allowedActions' => $this->getAllowedActions()
                ]
            )
        );
    }

    /**
     * Return allowed actions for media gallery
     */
    private function getAllowedActions(): array
    {
        $allowedActions = [];
        foreach (self::ACL_IMAGE_ACTIONS as $key => $action) {
            if ($this->authorization->isAllowed($action)) {
                $allowedActions[] = $key;
            }
        }

        return $allowedActions;
    }
}
