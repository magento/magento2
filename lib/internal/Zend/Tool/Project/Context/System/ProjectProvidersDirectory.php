<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ProjectProvidersDirectory.php 23202 2010-10-21 15:08:15Z ralph $
 */

/**
 * @see Zend_Tool_Project_Context_Filesystem_Directory
 */
#require_once 'Zend/Tool/Project/Context/Filesystem/Directory.php';

/**
 * @see Zend_Tool_Project_Context_System_Interface
 */
#require_once 'Zend/Tool/Project/Context/System/Interface.php';

/**
 * @see Zend_Tool_Project_Context_System_NotOverwritable
 */
#require_once 'Zend/Tool/Project/Context/System/NotOverwritable.php';

/**
 * This class is the front most class for utilizing Zend_Tool_Project
 *
 * A profile is a hierarchical set of resources that keep track of
 * items within a specific project.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Context_System_ProjectProvidersDirectory
    extends Zend_Tool_Project_Context_Filesystem_Directory
    implements Zend_Tool_Project_Context_System_Interface,
               Zend_Tool_Project_Context_System_NotOverwritable
{

    /**
     * @var string
     */
    protected $_filesystemName = 'providers';

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ProjectProvidersDirectory';
    }
    
    public function loadProviders(Zend_Tool_Framework_Registry_Interface $registry)
    {
        if (file_exists($this->getPath())) {

            $providerRepository = $registry->getProviderRepository();
            
            foreach (new DirectoryIterator($this->getPath()) as $item) {
                if ($item->isFile() && (($suffixStart = strpos($item->getFilename(), 'Provider.php')) !== false)) {
                    $className = substr($item->getFilename(), 0, $suffixStart+8);
                    // $loadableFiles[$className] = $item->getPathname();
                    include_once $item->getPathname();
                    $providerRepository->addProvider(new $className());
                }
            }
        }
    }

}