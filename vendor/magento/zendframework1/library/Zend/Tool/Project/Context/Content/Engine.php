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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tool_Project_Context_Content_Engine_CodeGenerator
 */
#require_once 'Zend/Tool/Project/Context/Content/Engine/CodeGenerator.php';

/**
 * @see Zend_Tool_Project_Context_Content_Engine_Phtml
 */
#require_once 'Zend/Tool/Project/Context/Content/Engine/Phtml.php';

/**
 * This class is the front most class for utilizing Zend_Tool_Project
 *
 * A profile is a hierarchical set of resources that keep track of
 * items within a specific project.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Context_Content_Engine
{
    /**
     * @var Zend_Tool_Framework_Client_Storage
     */
    protected $_storage = null;

    /**
     * @var string
     */
    protected $_keyInStorage = 'project/content';

    /**
     * @var array
     */
    protected $_engines = array();

    /**
     * __construct()
     *
     * @param Zend_Tool_Framework_Client_Storage $storage
     */
    public function __construct(Zend_Tool_Framework_Client_Storage $storage)
    {
        $this->_storage = $storage;
        $this->_engines = array(
            new Zend_Tool_Project_Context_Content_Engine_CodeGenerator($storage, $this->_keyInStorage),
            new Zend_Tool_Project_Context_Content_Engine_Phtml($storage, $this->_keyInStorage),
            );
    }

    /**
     * getContent()
     *
     * @param Zend_Tool_Project_Context_Interface $context
     * @param string $methodName
     * @param mixed $parameters
     * @return string
     */
    public function getContent(Zend_Tool_Project_Context_Interface $context, $methodName, $parameters)
    {
        $content = null;

        foreach ($this->_engines as $engine) {
            if ($engine->hasContent($context, $methodName, $parameters)) {
                $content = $engine->getContent($context, $methodName, $parameters);

                if ($content != null) {
                    break;
                }

            }

        }

        if ($content == null) {
            return false;
        }

        return $content;
    }

}
