<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Regenerates generated code and DI configuration
 */
class GeneratedFiles
{
	/**
	 * Separator literal to assemble timer identifier from timer names
	 */
	const REGENERATE_FLAG = '/var/.regenerate';

	/**
	 * Clean generated code and DI configuration
	 *
	 * @param array $initParams
	 * @return void
	 */
	public function requestRegeneration($initParams)
	{
		if (file_exists(BP . self::REGENERATE_FLAG)) {
			$directoryList = new DirectoryList(BP, $initParams);
			$defaultPaths = $directoryList::getDefaultConfig();
			$generationPath = BP . '/' . $defaultPaths[DirectoryList::GENERATION][DirectoryList::PATH];
			$diPath = BP . '/' . $defaultPaths[DirectoryList::DI][DirectoryList::PATH];

			if (is_dir($generationPath)) {
				\Magento\Framework\Filesystem\Io\File::rmdirRecursive($generationPath);
			}
			if (is_dir($diPath)) {
				\Magento\Framework\Filesystem\Io\File::rmdirRecursive($diPath);
			}
			unlink(BP . self::REGENERATE_FLAG);
		}
	}
}
