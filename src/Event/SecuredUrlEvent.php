<?php
namespace Netliva\SymfonyFileHelperBundle\Event;


use Symfony\Component\EventDispatcher\Event;

class SecuredUrlEvent extends Event
{
	/**
	 * @var string
	 */
	private $media_file;
	/**
	 * @var string
	 */
	private $path_preffix;
	/**
	 * @var string
	 */
	private $path;

	public function __construct (string $media_file, string $path_preffix = "") {
		$this->media_file   = $media_file;
		$this->path_preffix = $path_preffix;
		$this->path         = $this->path_preffix.DIRECTORY_SEPARATOR.$this->media_file;
	}

	/**
	 * @return string
	 */
	public function getMediaFile (): string
	{
		return $this->media_file;
	}

	/**
	 * @param string $media_file
	 */
	public function setMediaFile (string $media_file): void
	{
		$this->media_file = $media_file;
	}

	/**
	 * @return string
	 */
	public function getPathPreffix (): string
	{
		return $this->path_preffix;
	}

	/**
	 * @param string $path_preffix
	 */
	public function setPathPreffix (string $path_preffix): void
	{
		$this->path_preffix = $path_preffix;
	}

	/**
	 * @return string
	 */
	public function getPath (): string
	{
		return $this->path;
	}

	/**
	 * @param string $path
	 */
	public function setPath (string $path): void
	{
		$this->path = $path;
	}

}
