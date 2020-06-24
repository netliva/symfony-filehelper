<?php
namespace Netliva\SymfonyFileHelperBundle\Models;

/**
 * Description of Document
 *
 * @author Manoj
 */

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class Document
{

	/** @var File */
	private $file;
	private $subDir;
	private $newName;
	private $overwrite = false;
	private $fileName;

	/** @var string */
	protected static $uploadDirectory  = "media/uploads";

	// Upload Klasörü
	static public function setUploadDirectory($dir)
	{
		self::$uploadDirectory = rtrim($dir, DIRECTORY_SEPARATOR);
	}

	static public function getUploadDirectory()
	{
		if (self::$uploadDirectory === null) {
			throw new \RuntimeException("Trying to access upload directory for profile files");
		}
		return self::$uploadDirectory;
	}

	// Alt Klasör
	public function setSubDirectory($dir)
	{
		$this->subDir = $dir;
	}

	public function getSubDirectory()
	{
		if ($this->subDir === null) {
			throw new \RuntimeException("Trying to access sub directory for profile files");
		}
		return $this->subDir;
	}


	public function setFile(File $file)
	{
		$this->file = $file;
	}

	public function getFile()
	{
		return $this->file;
	}

	public function getOriginalFileName()
	{
		return $this->file->getClientOriginalName();
	}

	public function getFileName()
	{
		return $this->fileName;
	}

	public function getAbsoluteDirectory()
	{
		return $this->getUploadDirectory().DIRECTORY_SEPARATOR.$this->getSubDirectory();
	}
	public function getAbsolutePath()
	{
		return $this->getUploadDirectory().DIRECTORY_SEPARATOR.$this->getSubDirectory().DIRECTORY_SEPARATOR.$this->getFileName();
	}
	public function getRelativePath()
	{
		return $this->getSubDirectory().DIRECTORY_SEPARATOR.$this->getFileName();
	}
	/**
	 * @return mixed
	 */
	public function getNewName ()
	{
		return $this->newName;
	}

	/**
	 * @return mixed
	 */
	public function getOverwrite ()
	{
		return $this->overwrite;
	}

	/**
	 * @param mixed $overwrite
	 */
	public function setOverwrite ($overwrite)
	{
		$this->overwrite = $overwrite;
	}

	/**
	 * @param mixed $newName
	 */
	public function setNewName ($newName)
	{
		$this->newName = $newName;
	}
	public function processFile()
	{
		if (! ($this->file instanceof UploadedFile) ) {
			return false;
		}

		$uploadFileMover = new UploadFileMover();
		$uploadFileMover->setFile($this->getFile());
		$uploadFileMover->setUploadBasePath($this->getUploadDirectory());
		$uploadFileMover->setRelativePath($this->getSubDirectory());
		if	($this->getNewName()) {
			$uploadFileMover->setNewName($this->getNewName());
		}
		if	($this->getOverwrite()) {
			$uploadFileMover->setOverwrite($this->getOverwrite());
		}

		try {
			$this->fileName = $uploadFileMover->moveUploadedFile();
			$this->setFile(new File($this->getAbsolutePath()));
			return $this->getFileName();
		}
		catch (\Exception $exception)
		{
			return false;
		}
	}
}

