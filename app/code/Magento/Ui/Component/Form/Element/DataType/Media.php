<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Media
 */
class Media extends AbstractDataType
{
    const NAME = 'media';

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @param ContextInterface $context
     * @param Repository $assetRepo
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        Repository $assetRepo,
        array $components = [],
        array $data = []
    ) {
        $this->assetRepo = $assetRepo;

        parent::__construct($context, $components, $data);
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');

        $config['placeholder'] = $this->assetRepo->getUrl('images/fam_bullet_disk.gif');

        $this->setData('config', $config);

        parent::prepare();
    }
}
