<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App\Filesystem;

use Magento\Framework\App\Filesystem;

/**
 * Class DirectoryList
 */
class DirectoryList extends \Magento\Framework\Filesystem\DirectoryList
{
    /**
     * Directory for dynamically generated public view files, relative to STATIC_VIEW_DIR
     */
    const CACHE_VIEW_REL_DIR = '_cache';

    /**
     * Directories configurations
     *
     * @var array
     */
    protected $directories = array(
        Filesystem::ROOT_DIR => array('path' => ''),
        Filesystem::APP_DIR => array('path' => 'app'),
        Filesystem::MODULES_DIR => array('path' => 'app/code'),
        Filesystem::CONFIG_DIR => array('path' => 'app/etc'),
        Filesystem::LIB_INTERNAL => array('path' => 'lib/internal'),
        Filesystem::VAR_DIR => array(
            'path' => 'var',
            'read_only' => false,
            'allow_create_dirs' => true,
            'permissions' => 0777
        ),
        Filesystem::CACHE_DIR => array(
            'path' => 'var/cache',
            'read_only' => false,
            'allow_create_dirs' => true,
            'permissions' => 0777
        ),
        Filesystem::LOG_DIR => array(
            'path' => 'var/log',
            'read_only' => false,
            'allow_create_dirs' => true,
            'permissions' => 0777
        ),
        Filesystem::DI_DIR => array('path' => 'var/di'),
        Filesystem::GENERATION_DIR => array('path' => 'var/generation'),
        Filesystem::HTTP => array('path' => ''),
        Filesystem::LOCALE_DIR => array('path' => 'app/i18n'),
        Filesystem::SYS_TMP_DIR => array(
            'path' => '',
            'read_only' => false,
            'allow_create_dirs' => true,
            'permissions' => 0777
        ),
        Filesystem::SESSION_DIR => array(
            'path' => 'var/session',
            'read_only' => false,
            'allow_create_dirs' => true,
            'permissions' => 0777
        )
    );

    /**
     * @param string $root
     * @param array $directories
     */
    public function __construct($root, array $directories = array())
    {
        $this->directories[Filesystem::SYS_TMP_DIR]['path'] = sys_get_temp_dir();
        parent::__construct($root, $directories);
    }
}
