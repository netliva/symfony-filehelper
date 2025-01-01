<?php

namespace Netliva\SymfonyFileHelperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="netliva_file_list")
 */
class FileList
{
	/**
	 * @var integer
	 *
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
    private $id;

	/**
	 * @var UploaderInterface
	 *
	 * @ORM\ManyToOne(targetEntity="UploaderInterface")
	 * @ORM\JoinColumn()
	 */
	private $addBy;

	/**
	 * @var UploaderInterface
	 *
	 * @ORM\ManyToOne(targetEntity="UploaderInterface")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $updateBy;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="`group`", type="string", length=255)
	 */
    private $group;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
    private $code;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
    private $addAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
    private $updateAt;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255)
	 */
    private $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="`desc`", type="text", nullable=true)
	 */
    private $desc;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
    private $path;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
    private $extention;


	/**
	 * @var array
	 *
	 * @ORM\Column(type="json", nullable=true)
	 */
    private $inPast;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=32, nullable=true)
	 */
    private $assess;

	/**
	 * @return int
	 */
	public function getId (): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId (int $id): void
	{
		$this->id = $id;
	}

	/**
	 * @return UploaderInterface
	 */
	public function getAddBy (): UploaderInterface
	{
		return $this->addBy;
	}

	/**
	 * @param UploaderInterface $addBy
	 */
	public function setAddBy (UploaderInterface $addBy): void
	{
		$this->addBy = $addBy;
	}

	/**
	 * @return UploaderInterface
	 */
	public function getUpdateBy (): ?UploaderInterface
	{
		return $this->updateBy;
	}

	/**
	 * @param UploaderInterface $updateBy
	 */
	public function setUpdateBy (UploaderInterface $updateBy): void
	{
		$this->updateBy = $updateBy;
	}

	/**
	 * @return string
	 */
	public function getGroup (): string
	{
		return $this->group;
	}

	/**
	 * @param string $group
	 */
	public function setGroup (string $group): void
	{
		$this->group = $group;
	}

	/**
	 * @return string
	 */
	public function getCode (): string
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode (string $code): void
	{
		$this->code = $code;
	}

	/**
	 * @return \DateTime
	 */
	public function getAddAt (): \DateTime
	{
		return $this->addAt;
	}

	/**
	 * @param \DateTime $addAt
	 */
	public function setAddAt (\DateTime $addAt): void
	{
		$this->addAt = $addAt;
	}

	/**
	 * @return \DateTime
	 */
	public function getUpdateAt (): ?\DateTime
	{
		return $this->updateAt;
	}

	/**
	 * @param \DateTime $updateAt
	 */
	public function setUpdateAt (\DateTime $updateAt): void
	{
		$this->updateAt = $updateAt;
	}

	/**
	 * @return string
	 */
	public function getName (): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName (string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getDesc (): ?string
	{
		return $this->desc;
	}

	/**
	 * @param string $desc
	 */
	public function setDesc (?string $desc): void
	{
		$this->desc = $desc;
	}

	/**
	 * @return string
	 */
	public function getPath (): ?string
	{
		return $this->path;
	}

	/**
	 * @param string $path
	 */
	public function setPath (?string $path): void
	{
		$this->path = $path;
	}

	/**
	 * @return string
	 */
	public function getExtention (): ?string
	{
		return $this->extention;
	}

	/**
	 * @param string $extention
	 */
	public function setExtention (?string $extention): void
	{
		$this->extention = $extention;
	}

	/**
	 * @return array
	 */
	public function getInPast (): ?array
	{
		return $this->inPast;
	}

	/**
	 * @param array $inPast
	 */
	public function setInPast (array $inPast): void
	{
		$this->inPast = $inPast;
	}

	/**
	 * @return string
	 */
	public function getAssess (): ?string
	{
		return $this->assess;
	}

	/**
	 * @param string $assess
	 */
	public function setAssess (?string $assess): void
	{
		$this->assess = $assess;
	}



}
