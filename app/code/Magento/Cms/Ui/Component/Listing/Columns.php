<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Ui\Component\Listing;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Listing component columns.
 */
class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * Authorization.
     *
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param ContextInterface $context
     * @param UiComponentInterface[] $components
     * @param array $data
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = [],
        \Magento\Framework\AuthorizationInterface $authorization = null
    ) {
        parent::__construct($context, $components, $data);
        $this->authorization = $authorization ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\AuthorizationInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        parent::prepare();
        $this->applyEditPermission();
    }

    /**
     * Applying InlineEditor permission.
     *
     * @return void
     */
    private function applyEditPermission()
    {
        if (!$this->authorization->isAllowed('Magento_Cms::save')) {
            $editPermissions = [
                'config' => [
                    'editorConfig' => [
                        'enabled' => false,
                    ],
                ],
            ];
            $data = $this->getData();
            $data = array_replace_recursive($data, $editPermissions);
            $this->setData($data);
        }
    }
}
