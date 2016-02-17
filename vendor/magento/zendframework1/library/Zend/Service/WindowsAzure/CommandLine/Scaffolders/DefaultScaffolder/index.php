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
 * @package    Zend_Service_WindowsAzure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure_CommandLine
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Scaffolder
	extends Zend_Service_WindowsAzure_CommandLine_PackageScaffolder_PackageScaffolderAbstract
{
	/**
	 * Invokes the scaffolder.
	 *
	 * @param Phar $phar Phar archive containing the current scaffolder.
	 * @param string $root Path Root path.
	 * @param array $options Options array (key/value).
	 */
	public function invoke(Phar $phar, $rootPath, $options = array())
	{
		// Check required parameters
		if (empty($options['DiagnosticsConnectionString'])) {
			#require_once 'Zend/Service/Console/Exception.php';
			throw new Zend_Service_Console_Exception('Missing argument for scaffolder: DiagnosticsConnectionString');
		}

		// Extract to disk
		$this->log('Extracting resources...');
		$this->createDirectory($rootPath);
		$this->extractResources($phar, $rootPath);
		$this->log('Extracted resources.');

		// Apply transforms
		$this->log('Applying transforms...');
		$this->applyTransforms($rootPath, $options);
		$this->log('Applied transforms.');

		// Show "to do" message
		$contentRoot = realpath($rootPath . '/PhpOnAzure.Web');
		echo "\r\n";
		echo "Note: before packaging your application, please copy your application code to $contentRoot";
	}
}
