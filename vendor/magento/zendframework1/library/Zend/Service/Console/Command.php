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
 * @package    Zend_Service_Console
 * @version    $Id$
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @copyright  Copyright (c) 2009 - 2011, RealDolmen (http://www.realdolmen.com)
 * @license    http://phpazure.codeplex.com/license
 */

/**
 * @category   Zend
 * @package    Zend_Service_Console
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @copyright  Copyright (c) 2009 - 2011, RealDolmen (http://www.realdolmen.com)
 * @license    http://phpazure.codeplex.com/license
 */
class Zend_Service_Console_Command
{
	/**
	 * The handler.
	 *
	 * @var array
	 */
	protected $_handler;

	/**
	 * Gets the handler.
	 *
	 * @return array
	 */
	public function getHandler()
	{
		return $this->_handler;
	}

	/**
	 * Sets the handler.
	 *
	 * @param array $handler
	 * @return Zend_Service_Console_Command
	 */
	public function setHandler($handler)
	{
		$this->_handler = $handler;
		return $this;
	}

	/**
	 * Replaces PHP's error handler
	 *
	 * @param mixed $errno
	 * @param mixed $errstr
	 * @param mixed $errfile
	 * @param mixed $errline
	 */
	public static function phpstderr($errno, $errstr, $errfile, $errline)
	{
		self::stderr($errno . ': Error in ' . $errfile . ':' . $errline . ' - ' . $errstr);
	}

	/**
	 * Replaces PHP's exception handler
	 *
	 * @param Exception $exception
	 */
	public static function phpstdex($exception)
	{
		self::stderr('Error: ' . $exception->getMessage());
	}

	/**
	 * Writes output to STDERR, followed by a newline (optional)
	 *
	 * @param string $errorMessage
	 * @param string $newLine
	 */
	public static function stderr($errorMessage, $newLine = true)
	{
		if (error_reporting() === 0) {
			return;
		}
		file_put_contents('php://stderr', $errorMessage . ($newLine ? "\r\n" : ''));
	}

	/**
	 * Bootstrap the shell command.
	 *
	 * @param array $argv PHP argument values.
	 */
	public static function bootstrap($argv)
	{
		// Abort bootstrapping depending on the MICROSOFT_CONSOLE_COMMAND_HOST constant.
		if (defined('MICROSOFT_CONSOLE_COMMAND_HOST') && strtolower(MICROSOFT_CONSOLE_COMMAND_HOST) != 'console') {
			return;
		}

		// Replace error handler
		set_error_handler(array('Zend_Service_Console_Command', 'phpstderr'));
		set_exception_handler(array('Zend_Service_Console_Command', 'phpstdex'));

		// Build the application model
		$model = self::_buildModel();

		// Find a class that corresponds to the $argv[0] script name
		$requiredHandlerName = str_replace('.bat', '', str_replace('.sh', '', str_replace('.php', '', strtolower(basename($argv[0])))));
		$handler = null;
		foreach ($model as $possibleHandler) {
			if ($possibleHandler->handler == strtolower($requiredHandlerName)) {
				$handler = $possibleHandler;
				break;
			}
		}
		if (is_null($handler)) {
			self::stderr("No class found that implements handler '" . $requiredHandlerName . "'. Create a class that is named '" . $requiredHandlerName . "' and extends Zend_Service_Console_Command or is decorated with a docblock comment '@command-handler " . $requiredHandlerName . "'. Make sure it is loaded either through an autoloader or explicitly using require_once().");
			die();
		}

		// Find a method that matches the command name
		$command = null;
		foreach ($handler->commands as $possibleCommand) {
			if (in_array(strtolower(isset($argv[1]) ? $argv[1] : '<default>'), $possibleCommand->aliases)) {
				$command = $possibleCommand;
				break;
			}
		}
		if (is_null($command)) {
			$commandName = (isset($argv[1]) ? $argv[1] : '<default>');
			self::stderr("No method found that implements command " . $commandName . ". Create a method in class '" . $handler->class . "' that is named '" . strtolower($commandName) . "Command' or is decorated with a docblock comment '@command-name " . $commandName . "'.");
			die();
		}

		// Parse parameter values
		$parameterValues = array();
		$missingParameterValues = array();
		$parameterInputs = array_splice($argv, 2);
		foreach ($command->parameters as $parameter) {
			// Default value: null
			$value = null;

			// Consult value providers for value. First one wins.
			foreach ($parameter->valueproviders as $valueProviderName) {
				if (!class_exists($valueProviderName)) {
					$valueProviderName = 'Zend_Service_Console_Command_ParameterSource_' . $valueProviderName;
				}
				$valueProvider = new $valueProviderName();

				$value = $valueProvider->getValueForParameter($parameter, $parameterInputs);
				if (!is_null($value)) {
					break;
				}
			}
			if (is_null($value) && $parameter->required) {
				$missingParameterValues[] = $parameter->aliases[0];
			} else if (is_null($value)) {
				$value = $parameter->defaultvalue;
			}

			// Set value
			$parameterValues[] = $value;
			$argvValues[$parameter->aliases[0]] = $value;
		}

		// Mising parameters?
		if (count($missingParameterValues) > 0) {
			self::stderr("Some parameters are missing:\r\n" . implode("\r\n", $missingParameterValues));
			die();
		}

		// Supply argv in a nice way
		$parameterValues['argv'] = $parameterInputs;

		// Run the command
		$className = $handler->class;
		$classInstance = new $className();
		$classInstance->setHandler($handler);
		call_user_func_array(array($classInstance, $command->method), $parameterValues);

		// Restore error handler
		restore_exception_handler();
		restore_error_handler();
	}

	/**
	 * Builds the handler model.
	 *
	 * @return array
	 */
	protected static function _buildModel()
	{
		$model = array();

		$classes = get_declared_classes();
		foreach ($classes as $class) {
			$type = new ReflectionClass($class);

			$handlers = self::_findValueForDocComment('@command-handler', $type->getDocComment());
			if (count($handlers) == 0 && $type->isSubclassOf('Zend_Service_Console_Command')) {
				// Fallback: if the class extends Zend_Service_Console_Command, register it as
				// a command handler.
				$handlers[] = $class;
			}
			$handlerDescriptions = self::_findValueForDocComment('@command-handler-description', $type->getDocComment());
			$handlerHeaders = self::_findValueForDocComment('@command-handler-header', $type->getDocComment());
			$handlerFooters = self::_findValueForDocComment('@command-handler-footer', $type->getDocComment());

			for ($hi = 0; $hi < count($handlers); $hi++) {
				$handler = $handlers[$hi];
				$handlerDescription = isset($handlerDescriptions[$hi]) ? $handlerDescriptions[$hi] : isset($handlerDescriptions[0]) ? $handlerDescriptions[0] : '';
				$handlerDescription = str_replace('\r\n', "\r\n", $handlerDescription);
				$handlerDescription = str_replace('\n', "\n", $handlerDescription);

				$handlerModel = (object)array(
					'handler'     => strtolower($handler),
					'description' => $handlerDescription,
					'headers'     => $handlerHeaders,
					'footers'     => $handlerFooters,
					'class'       => $class,
					'commands'    => array()
				);

				$methods = $type->getMethods();
			    foreach ($methods as $method) {
			       	$commands = self::_findValueForDocComment('@command-name', $method->getDocComment());
			    	if (substr($method->getName(), -7) == 'Command' && !in_array(substr($method->getName(), 0, -7), $commands)) {
						// Fallback: if the method is named <commandname>Command,
						// register it as a command.
						$commands[] = substr($method->getName(), 0, -7);
					}
			       	for ($x = 0; $x < count($commands); $x++) {
			       		$commands[$x] = strtolower($commands[$x]);
			       	}
			       	$commands = array_unique($commands);
			       	$commandDescriptions = self::_findValueForDocComment('@command-description', $method->getDocComment());
			       	$commandExamples = self::_findValueForDocComment('@command-example', $method->getDocComment());

			       	if (count($commands) > 0) {
						$command = $commands[0];
						$commandDescription = isset($commandDescriptions[0]) ? $commandDescriptions[0] : '';

						$commandModel = (object)array(
							'command'     => $command,
							'aliases'     => $commands,
							'description' => $commandDescription,
							'examples'    => $commandExamples,
							'class'       => $class,
							'method'      => $method->getName(),
							'parameters'  => array()
						);

						$parameters = $method->getParameters();
						$parametersFor = self::_findValueForDocComment('@command-parameter-for', $method->getDocComment());
						for ($pi = 0; $pi < count($parameters); $pi++) {
							// Initialize
							$parameter = $parameters[$pi];
							$parameterFor = null;
							$parameterForDefaultValue = null;

							// Is it a "catch-all" parameter?
							if ($parameter->getName() == 'argv') {
								continue;
							}

							// Find the $parametersFor with the same name defined
							foreach ($parametersFor as $possibleParameterFor) {
								$possibleParameterFor = explode(' ', $possibleParameterFor, 4);
								if ($possibleParameterFor[0] == '$' . $parameter->getName()) {
									$parameterFor = $possibleParameterFor;
									break;
								}
							}
							if (is_null($parameterFor)) {
								die('@command-parameter-for missing for parameter $' . $parameter->getName());
							}

							if (is_null($parameterForDefaultValue) && $parameter->isOptional()) {
								$parameterForDefaultValue = $parameter->getDefaultValue();
							}

							$parameterModel = (object)array(
								'name'           => '$' . $parameter->getName(),
								'defaultvalue'   => $parameterForDefaultValue,
								'valueproviders' => explode('|', $parameterFor[1]),
								'aliases'        => explode('|', $parameterFor[2]),
								'description'    => (isset($parameterFor[3]) ? $parameterFor[3] : ''),
								'required'       => (isset($parameterFor[3]) ? strpos(strtolower($parameterFor[3]), 'required') !== false && strpos(strtolower($parameterFor[3]), 'required if') === false : false),
							);

							// Add to model
							$commandModel->parameters[] = $parameterModel;
						}

						// Add to model
						$handlerModel->commands[] = $commandModel;
			       	}
				}

				// Add to model
				$model[] = $handlerModel;
			}
		}

		return $model;
	}

	/**
	 * Finds the value for a specific docComment.
	 *
	 * @param string $docCommentName Comment name
	 * @param unknown_type $docComment Comment object
	 * @return array
	 */
	protected static function _findValueForDocComment($docCommentName, $docComment)
	{
		$returnValue = array();

		$commentLines = explode("\n", $docComment);
	    foreach ($commentLines as $commentLine) {
	        if (strpos($commentLine, $docCommentName . ' ') !== false) {
	            $returnValue[] = trim(substr($commentLine, strpos($commentLine, $docCommentName) + strlen($docCommentName) + 1));
	        }
	    }

	    return $returnValue;
	}

	/**
	 * Display information on an object
	 *
	 * @param object $object Object
	 * @param array $propertiesToDump Property names to display
	 */
	protected function _displayObjectInformation($object, $propertiesToDump = array())
	{
		foreach ($propertiesToDump as $property) {
			printf('%-16s: %s' . "\r\n", $property, $object->$property);
		}
		printf("\r\n");
	}

	/**
	 * Displays the help information.
	 *
	 * @command-name <default>
	 * @command-name -h
	 * @command-name -help
	 * @command-description Displays the current help information.
	 */
	public function helpCommand() {
		$handler = $this->getHandler();
		$newline = "\r\n";

		if (count($handler->headers) > 0) {
			foreach ($handler->headers as $header) {
				printf('%s%s', $header, $newline);
			}
			printf($newline);
		}
		printf('%s%s', $handler->description, $newline);
		printf($newline);
		printf('Available commands:%s', $newline);
		foreach ($handler->commands as $command) {
			$description = str_split($command->description, 50);
			printf('  %-25s %s%s', implode(', ', $command->aliases), $description[0], $newline);
			for ($di = 1; $di < count($description); $di++) {
				printf('  %-25s %s%s', '', $description[$di], $newline);
			}
			printf($newline);

			if (count($command->parameters) > 0) {
				foreach ($command->parameters as $parameter) {
					$description = str_split($parameter->description, 50);
					printf('    %-23s %s%s', implode(', ', $parameter->aliases), $description[0], $newline);
					for ($di = 1; $di < count($description); $di++) {
						printf('    %-23s %s%s', '', $description[$di], $newline);
					}
					printf($newline);
				}
			}
			printf($newline);

			if (count($command->examples) > 0) {
				printf('    Example usage:%s', $newline);
				foreach ($command->examples as $example) {
					printf('      %s%s', $example, $newline);
				}
				printf($newline);
			}
		}

		if (count($handler->footers) > 0) {
			printf($newline);
			foreach ($handler->footers as $footer) {
				printf('%s%s', $footer, $newline);
			}
			printf($newline);
		}
	}
}
