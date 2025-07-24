<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg;

use Magento\Framework\DataObject;
use Magento\Framework\View\Asset\Repository;

/**
 * Class DefaultConfigProvider returns data required to render tinymce editor
 */
class DefaultConfigProvider implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * @var Repository
     */
    private Repository $assetRepo;

    /**
     * @param Repository $assetRepo
     */
    public function __construct(Repository $assetRepo)
    {
        $this->assetRepo = $assetRepo;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(DataObject $config) : DataObject
    {
        $config->addData([
            'tinymce' => [
                'toolbar' => ' blocks fontfamily fontsizeinput| formatselect | bold italic underline ' .
                    '| alignleft aligncenter alignright | bullist numlist | link table charmap',
                'plugins' => implode(
                    ' ',
                    [
                        'advlist',
                        'autolink',
                        'lists',
                        'link',
                        'charmap',
                        'media',
                        'table',
                        'code',
                        'help',
                        'table'
                    ]
                ),
                'content_css' => $this->assetRepo->getUrl('mage/adminhtml/wysiwyg/tiny_mce/themes/ui.css')
            ]
        ]);
        return $config;
    }
}
