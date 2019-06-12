<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\Component\Product;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;

<<<<<<< HEAD
/**
 * Provide validation of allowed massaction for user.
 */
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
class MassAction extends AbstractComponent
{
    const NAME = 'massaction';

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
<<<<<<< HEAD
=======
     * Constructor
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param AuthorizationInterface $authorization
     * @param ContextInterface $context
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->authorization = $authorization;
        parent::__construct($context, $components, $data);
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
     */
    public function prepare()
=======
     * {@inheritdoc}
     */
    public function prepare() : void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $config = $this->getConfiguration();

        foreach ($this->getChildComponents() as $actionComponent) {
            $actionType = $actionComponent->getConfiguration()['type'];
            if ($this->isActionAllowed($actionType)) {
                $config['actions'][] = $actionComponent->getConfiguration();
            }
        }
        $origConfig = $this->getConfiguration();
        if ($origConfig !== $config) {
            $config = array_replace_recursive($config, $origConfig);
        }

        $this->setData('config', $config);
        $this->components = [];

        parent::prepare();
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
     */
    public function getComponentName(): string
=======
     * {@inheritdoc}
     */
    public function getComponentName() : string
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return static::NAME;
    }

    /**
<<<<<<< HEAD
     * Check if the given type of action is allowed.
=======
     * Check if the given type of action is allowed
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     *
     * @param string $actionType
     * @return bool
     */
<<<<<<< HEAD
    public function isActionAllowed(string $actionType): bool
=======
    public function isActionAllowed($actionType) : bool
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $isAllowed = true;
        switch ($actionType) {
            case 'delete':
<<<<<<< HEAD
=======
                $isAllowed = $this->authorization->isAllowed('Magento_Catalog::products');
                break;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            case 'status':
                $isAllowed = $this->authorization->isAllowed('Magento_Catalog::products');
                break;
            case 'attributes':
                $isAllowed = $this->authorization->isAllowed('Magento_Catalog::update_attributes');
                break;
            default:
                break;
        }
<<<<<<< HEAD

=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $isAllowed;
    }
}
