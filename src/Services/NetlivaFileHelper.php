<?phpnamespace Netliva\SymfonyFileHelperBundle\Services;use Doctrine\ORM\EntityManager;use Netliva\SymfonyFileHelperBundle\Entity\FileList;use Netliva\SymfonyFileHelperBundle\Event\NetlivaFileHelperEvents;use Netliva\SymfonyFileHelperBundle\Event\PublicUrlEvent;use Netliva\SymfonyFileHelperBundle\Event\SecuredUrlEvent;use Symfony\Component\Asset\Package;use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;use Symfony\Component\DependencyInjection\ContainerInterface;use Symfony\Component\HttpFoundation\Request;use Twig\Environment;use Twig\Extension\AbstractExtension;use Twig\TwigFilter;use Twig\TwigFunction;/** * A TWIG Extension which allows to show Controller and Action name in a TWIG view. * * The Controller/Action name will be shown in lowercase. For example: 'default' or 'index' * */class NetlivaFileHelper extends AbstractExtension{    /**     * @var Request     */    protected $request;    /** @var ContainerInterface */    protected $container;    /** @var EntityManager */	protected $em;	/**	 * @var Environment	 */	private $twig;	public function __construct($em, ContainerInterface $container, Environment $twig){		$this->container = $container;		$this->em        = $em;		$this->twig      = $twig;	}    public function setRequest(Request $request = null)    {        $this->request = $request;    }    public function getFilters ()	{		return [			new TwigFilter('get_extention', [$this, 'getExtention']),			new TwigFilter('uploaded_count', [$this, 'uploadedCount']),			new TwigFilter('is_file_control', [$this, 'isFileControl']),			new TwigFilter('is_optional', [$this, 'isOptional']),			new TwigFilter('get_file_thumbnail', [$this, 'getFileThumbnail']),			new TwigFilter('is_prepare_information', [$this, 'isPrepareInformation']),			new TwigFilter('is_true', [$this, 'isTrue']),		];	}    public function getFunctions()    {        return array(			new TwigFunction('secure_media_uri', [$this, 'mediaSecureUri']),			new TwigFunction('public_media_uri', [$this, 'mediaPublicUri']),            new TwigFunction('get_file_path', [$this, 'getFilePath'],array('is_safe' => array('html'))),            new TwigFunction('get_file_path_if_exist', [$this, 'getFilePathIfExist'],array('is_safe' => array('html'))),            new TwigFunction('get_file', [$this, 'getFile']),            new TwigFunction('get_stack', [$this, 'getStack']),            new TwigFunction('show_file', [$this, 'showFile'],array('is_safe' => array('html'))),            new TwigFunction('get_hard_file_list', [$this, 'getHardFileList'],array('is_safe' => array('html'))),            new TwigFunction('get_soft_file_list', [$this, 'getSoftFileList'],array('is_safe' => array('html'))),            new TwigFunction('file_uploader_button', [$this, 'fileUploaderButton'],array('is_safe' => array('html'))),            new TwigFunction('file_uploader_widget', [$this, 'fileUploader'],array('is_safe' => array('html'))),			new TwigFunction('netliva_file_exists', [$this, 'fileExists']),        );    }	public function mediaSecureUri($path)	{		$config          = $this->container->getParameter("netliva_filehelper.config");		$eventDispatcher = $this->container->get('event_dispatcher');		$event           = new PublicUrlEvent($path, $config["secure_uri_prefix"]);		$eventDispatcher->dispatch(NetlivaFileHelperEvents::PUBLIC_URL, $event);		return $event->getPath();	}	public function mediaPublicUri($path)	{        if (!$path)            return null;		$config          = $this->container->getParameter("netliva_filehelper.config");		$eventDispatcher = $this->container->get('event_dispatcher');		$event           = new SecuredUrlEvent($path, $config["public_uri_prefix"]);		$eventDispatcher->dispatch(NetlivaFileHelperEvents::SECURED_URL, $event);		return $event->getPath();	}	public function isFileControl ($options)	{		if (is_object($options)) $options = (array)$options;		return $options and is_array($options) and key_exists("control", $options) and $options["control"] and $options["control"] !== "false" and $options["control"] !== "0";    }	public function isOptional (array $options, array $value, string $key)	{		if (key_exists("requirement", $options))		{			if (is_string($options["requirement"]))			{				if ($options["requirement"] === "required") return false;				if ($options["requirement"] === "optional") return true;			}			elseif (is_array($options["requirement"]) and count($options["requirement"]) and key_exists($key, $options["requirement"]))			{				if ($options["requirement"][$key] === "required") return false;				if ($options["requirement"][$key] === "optional") return true;			}		}		if (key_exists("optional", $value) and $value["optional"] and $value["optional"] !== "false" and $value["optional"] !== "0") return true;		return false;	}	public function isPrepareInformation ($options)	{		if (is_object($options)) $options = (array)$options;		return $options and is_array($options) and key_exists("prepare_information", $options) and $options["prepare_information"] and $options["prepare_information"] !== "false" and $options["prepare_information"] !== "0";    }	public function istrue ($value)	{		return $value and $value !== "false" and $value !== "0";    }	public function uploadedCount ($children, $group, $keyPrefix)	{		$total = 0;		foreach($children as $key => $info)		{			$file = $this->getFile($group, $keyPrefix."_".$key);			if($file && file_exists($file->getPath())) $total++;		}		return $total;	}	private function getPathString ($path)	{		if ($path instanceof FileList) $path = $path->getPath();		if (is_array($path) and key_exists("path", $path)) $path = $path["path"];		if (!is_string($path)) $path = null;		return $path;	}	public function getExtention ($path)	{		$path = $this->getPathString($path);		if (!$path or !$this->fileExists($path)) return 'notfound';		return pathinfo($path, PATHINFO_EXTENSION);    }	public function getFileThumbnail ($path, $iconSize)	{		$path = $this->getPathString($path);		$thumb = "/bundles/netlivasymfonyfilehelper/images/file-icons/".$this->getExtention($path)."-".$iconSize.".png";		$root = $this->container->getParameter("kernel.project_dir");		if (!file_exists($root."/public".$thumb) && !file_exists($root."/web".$thumb))			$thumb = "/bundles/netlivasymfonyfilehelper/images/file-icons/unknown-".$iconSize.".png";		$package = new Package(new EmptyVersionStrategy());		return $package->getUrl($thumb);    }	public function hashFilePath ($path)	{		$path = preg_replace('/media\/uploads\//', '', $path);		return 'hash/media/uploads/'.$this->encode($path);	}	public function fileExists($path)    {		$config = $this->container->getParameter("netliva_filehelper.config");		return file_exists($config["upload_path"].$path);    }    /** @return FileList */	public function getFile($fileGroup, $fileCode)    {		if	($fileGroup == "byid")			return $this->em->getRepository(FileList::class)->find($fileCode);		return $this->em->getRepository(FileList::class)->findOneBy(["group"=>$fileGroup,"code"=>$fileCode]);    }	public function getStack($fileGroup, $fileCode = null)    {		$qb = $this->em->getRepository(FileList::class)->createQueryBuilder("fl");		$qb->where($qb->expr()->eq("fl.group", ":fl_grb"));		$qb->setParameter("fl_grb", $fileGroup);		if ($fileCode)		{			$qb->andWhere($qb->expr()->like("fl.code", ":fl_code"));			$qb->setParameter("fl_code", $fileCode."_%");		}		/*		$qb->andWhere(			$qb->expr()->orX(				$qb->expr()->isNotNull("fl.path"),				$qb->expr()->eq("fl.assess", "'rejection'")			)		);		*/		return $qb->getQuery()->getResult();    }	public function getFilePath($fileGroup, $fileCode, $date=null, $ifExist = false)	{		$file = $this->getFile($fileGroup, $fileCode);		$path =  null;		if ($file)		{			if (!$date) $date = new \DateTime();			if ($date >= $file->getUpdateAt()) $path = $file->getPath();			else			{				$lastPath = $file->getPath();				if (is_array($file->getInPast()))				{					foreach (array_reverse($file->getInPast()) as $past)					{						$lastPath = $past["path"];						if ($date >= new \DateTime($past["addAt"]["date"]))							$path = $past["path"];					}				}				if (!$path and $lastPath) $path = $lastPath;			}		}		$config = $this->container->getParameter("netliva_filehelper.config");		if ($path) $path = $config["upload_path"].$path;		if ($path && (!$ifExist || file_exists($path)))			return $path;		return null;	}	public function getFilePathIfExist($fileGroup, $fileCode)    {		return $this->getFilePath($fileGroup, $fileCode, null, true);    }	public function showFile($fileGroup, $fileCode=null, $opt=null)    {		$default = [			"style" => "line", // line, hover, click, icon, link			"icon_size" => 128, // 128, 32			"hash"		=> false		];		if (!is_array($opt)) $opt = [];		$opt = array_merge($default, $opt);		$file = null;		if ($fileCode)		{			$file = $this->getFile($fileGroup, $fileCode);		}        return $this->twig->render('@NetlivaSymfonyFileHelper/showFile.html.twig', [			"fileGroup" => $fileGroup,			"fileCode"  => $fileCode,			"opt"       => $opt,			"file"      => $file,		]);	}    /**    * Get current controller name    */    public function getHardFileList($listId, $fileGroup, $options = [])    {		$default = [			"hide_stack_delete_btn" => false, // yüklenen stack odsyanın silinmesi			"allow_delete"          => false, // yüklenen odsyanın silinmesi			"filter"                => null,			"subDir"                => null,			"rename"                => true,			"upload_btn"            => true,			"requirement"           => [], // özel olarak gereklilik veya opsiyon belirtilmek istenirse, buradan dosyanın anahtarı ile eklenecek. Örn: 3 => "required", 5=>"optional" veya tümü için direk array yerine string "required" veya "optional" belirtilmeli			"all_approve_btn"		=> false, // tüm listeyi onaylama işlemi		];		if (is_object($options)) $options = (array)$options;		elseif (!is_array($options)) $options = [];		$options = array_merge($default, $options);		$fileList = $this->container->getParameter('netliva_filehelper.file_list');		$fileList = $fileList[$listId];		return $this->twig->render('@NetlivaSymfonyFileHelper/List.hard.html.twig',		   [			   "group"    => $fileGroup,			   "listId"   => $listId,			   "fileList" => $fileList,			   'options'  => $options,		   ]		);    }    public function getSoftFileListDatas($options)    {		if (!key_exists("group",$options)) { echo ('HATA: Dosya Grup Bilgisi Gerekli!'); return false; }		$default = [			"title"      => "Dosya Listesi",			"desc"       => "Dokümanları listeleyip, yeni doküman ekleyebilirsiniz.",			"deletable"  => false,			"subDir"     => null,			"upload_btn" => true,		];		$options = array_merge($default, $options);        $uploadedFiles = $this->em->getRepository(FileList::class)->findByGroup($options['group']);        return [			"options"       => $options,			"uploadedFiles" => $uploadedFiles,		];    }    public function getSoftFileList($options)    {        return $this->twig->render('@NetlivaSymfonyFileHelper/List.soft.html.twig', $this->getSoftFileListDatas($options));    }    public function fileUploaderButton($fileGroup, $fileCode=null, $listId=null, $opt=null)    {		// maxDate : seçilecek en yüksek tarih		// minDate : seçilecek en düşük tarih		$default = [			"selectDate"      => false, // dosya yüklerken tarih iste			"getName"         => true, // dosya yüklerken, dosya adı iste			"style"           => "hover", // detayları görüntüleme şekli (line, hover, click)			"name"            => "", // ön tanımlı dosya tanımı			"info"            => "show", // Yükleyen ve yükleme tarihi bilgileri gözüksün mü			"hard_stack_list" => [], // Eğer stack alanıysa, yüklenecek dosyaların neler olabileceğinin listesi			"subDir"          => null,			"rename"          => false,			"multiupload"     => false,			"button_desc"     => false,		];		if (is_object($opt)) $opt = (array)$opt;		elseif (!is_array($opt)) $opt = [];		$opt = array_merge($default, $opt);		if (count($opt["hard_stack_list"])) $opt["getName"] = false;		if (!$opt['subDir']) $opt['subDir'] = $this->guessSubdir($fileGroup, $fileCode);		$isUplodedBefore = false;		if ($fileCode)		{			$file = $this->getFile($fileGroup, $fileCode);			if ($file) $isUplodedBefore = true;		}		return $this->twig->render('@NetlivaSymfonyFileHelper/fileUploaderButton.html.twig',		   [			   "fileGroup"       => $fileGroup,			   "listId"          => $listId,			   "fileCode"        => $fileCode,			   "opt"             => $opt,			   "isUplodedBefore" => $isUplodedBefore,		   ]		);	}	private function guessSubdir ($fileGroup, $fileCode)	{		$dir = "";		foreach (explode("_",$fileGroup) as $temp)		{			foreach (explode("-",$temp) as $str)			{				if (!is_numeric($str) && !preg_match("/\//", $dir)) $dir .= "_";				else $dir .= "/";				$dir .= $str;			}		}		$dir = trim($dir,"/_");		return $dir.($fileCode?"/".$fileCode:"");    }	public function fileUploader($fileGroup, $fileCode=null, $opt=null)    {		$default = [			"selectDate" => false,			"getName"    => true,			"style"      => "hover",			"name"       => "",			"info"       => "show",			"subDir"     => null,			"rename"     => true,			"optional"   => false,			"upload_btn" => true,		];		if (!is_array($opt)) $opt = [];		$opt = array_merge($default, $opt);		$file = null;		if ($fileCode)		{			$file = $this->em->getRepository(FileList::class)->findOneBy(["group"=>$fileGroup,"code"=>$fileCode]);		}		return $this->twig->render('@NetlivaSymfonyFileHelper/fileUploaderContainer.html.twig',[			"fileGroup"	=> $fileGroup,			"listId"	=> null,			"fileCode"	=> $fileCode,			"opt"		=> $opt,			"file"		=> $file,		]);	}	public function encode($string, $url_encode = false)	{		$key = getenv("APP_SECRET");		//$key previously generated safely, ie: openssl_random_pseudo_bytes		$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");		$iv = openssl_random_pseudo_bytes($ivlen);		$ciphertext_raw = openssl_encrypt($string, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);		$hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);		$ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );		if	($url_encode) {			return urlencode($ciphertext);		}		return $ciphertext;	}	public function decode($ciphertext, $url_decode = false)	{		$key = getenv("APP_SECRET");		//decrypt later....		if	($url_decode) {			$ciphertext = urldecode($ciphertext);		}		$c = base64_decode($ciphertext);		$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");		$iv = substr($c, 0, $ivlen);		$hmac = substr($c, $ivlen, $sha2len=32);		$ciphertext_raw = substr($c, $ivlen+$sha2len);		$reportId = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);		$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);		if (hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison		{			return $reportId;		}		return null;	}}