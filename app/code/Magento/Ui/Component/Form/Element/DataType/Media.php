<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Asset\Repository;

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

        $config['placeholder'] = $this->getAssetRepo()->getUrl('images/fam_bullet_disk.gif');

        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * Get Repository instance
     *
     * @return Repository
     *
     * @deprecated
     */
    private function getAssetRepo()
    {
        if ($this->assetRepo === null) {
            $this->assetRepo = ObjectManager::getInstance()->get(Repository::class);
        }
        return $this->assetRepo;
    }
}
