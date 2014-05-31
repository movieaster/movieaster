<?php

namespace Movieaster\MovieManagerBundle\Component;

use Symfony\Component\Finder\Finder;

class FileSystemUtil
{
	public static function allFolderNames($path)
	{
		$finder = new Finder();
		$filesFinder = $finder->files();
		$dirFinder = $filesFinder->in(__DIR__);
		$pathFinder = $dirFinder->in($path);
		$folders = $pathFinder->directories()->depth('== 0');
		$folderNames = array();
		foreach ($folders as $folder) {
			if($folder->getFilename() != "@eaDir")  {
				$folderNames[] = $folder->getFilename();
			}
		}
		return $folderNames;
	}
}
?>