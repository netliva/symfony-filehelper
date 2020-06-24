<?phpnamespace Netliva\SymfonyFileHelperBundle\Controller;use Netliva\SymfonyFileHelperBundle\Entity\FileList;use Netliva\SymfonyFileHelperBundle\Models\Document;use Symfony\Bundle\FrameworkBundle\Controller\Controller;use Symfony\Component\HttpFoundation\File\UploadedFile;use Symfony\Component\HttpFoundation\JsonResponse;use Symfony\Component\HttpFoundation\Request;use Symfony\Component\HttpFoundation\Response;class FileController extends Controller{	public function refreshLineAction(Request $request) {		$fileGroup = $request->get('fileGroup');		$fileCode = $request->get('fileCode');		$listId = $request->get('listId');		$options = $request->get('options');		if ($listId)		{			$fileInfo = $this->container->getParameter('netliva_filehelper.file_list');			$fileList = $fileInfo[$listId]["values"];			foreach (explode("_", $fileCode) as $code)			{				if (key_exists($code, $fileList))				{					$fileInfo = $fileList[$code];					if (key_exists("children", $fileInfo) && count($fileInfo["children"]))						$fileList = $fileInfo["children"];					else break;				}			}			return $this->render('NetlivaFileBundle:File:List.hard.line.html.twig', ["group"=>$fileGroup, "listId"=>$listId, "value"=>$fileInfo, "key" =>$fileCode, "options" =>$options, "ajaxload"=>true]);		}		return new Response("Dosya Bulunamadı");	}	public function refreshSingleWithIconAction(Request $request) {		$fileGroup = $request->get('fileGroup');		$fileCode = $request->get('fileCode');		$opt = json_decode($request->get('opt'));        $em = $this->getDoctrine()->getManager();		$file = $em->getRepository('NetlivaFileBundle:FileList')->findOneBy(["group"=>$fileGroup,"code"=>$fileCode]);        return $this->render('NetlivaFileBundle:File:fileUploader.html.twig', ["fileGroup"=>$fileGroup, "listId"=>null, "fileCode"=>$fileCode, "opt"=>$opt, "file"=>$file]);	}	public function refreshSoftListAction(Request $request)	{		$options = json_decode($request->get('opt'), true);		$nf = $this->get("netliva.files.twig");        return $this->render('NetlivaFileBundle:File:List.soft.lines.html.twig', $nf->getSoftFileListDatas($options));	}	public function refreshStackAction (Request $request)	{		$fileGroup = $request->get('fileGroup');		$uploadCode = $request->get('fileCode');		$options = $request->get('options');		$listId = $request->get('listId');		[$fileGroup, $fileCode] = explode("-",$fileGroup);		if ($listId)		{			$fileInfo = $this->container->getParameter('netliva_filehelper.file_list');			$fileList = $fileInfo[$listId]["values"];			foreach (explode("_", $fileCode) as $code)			{				if (key_exists($code, $fileList))				{					$fileInfo = $fileList[$code];					if (key_exists("children", $fileInfo) && count($fileInfo["children"]))						$fileList = $fileInfo["children"];					else break;				}			}			return $this->render('NetlivaFileBundle:File:List.stack.html.twig', ["group"=>$fileGroup, "listId"=>$listId, "value"=>$fileInfo, "key" =>$fileCode, "options" =>$options, "ajaxload"=>true]);		}		return new Response("Dosya Bulunamadı");	}	public function moveToOld ($path)	{		if ($path && file_exists($path))		{			$exploded = explode("/", $path);			$file_name = array_pop($exploded);			$newdir = implode("/", $exploded).DIRECTORY_SEPARATOR.".old".DIRECTORY_SEPARATOR;			if (!is_dir($newdir)) mkdir($newdir, 0777, true);			$i=1;			while (file_exists($newdir.$file_name))			{				$prev = $i == 1 ? "" : "(".$i. ")";				$i++;				$next =  "(".$i. ")";				if ($prev)				{					$file_name = str_replace($prev,  $next, $file_name);				}				else				{					$fNameExp = explode(".",$file_name);					$ext = null;					if (count($fNameExp)>1)					{						$ext = array_pop($fNameExp);					}					$file_name = implode(".", $fNameExp). $next. ($ext?".".$ext:"");				}			}			rename($path, $newdir.$file_name);			$path = $newdir.$file_name;		}		return $path;	}	public function createPastStructure (FileList $file, $oldPath)	{		return [			"addAt"     => $file->getUpdateAt() ? $file->getUpdateAt() : $file->getAddAt(),			"addBy"     => $file->getUpdateBy() ? ["id"   => $file->getUpdateBy()->getId(), "name" => $file->getUpdateBy()->getName()] : ["id" => $file->getAddBy()->getId(), "name" => $file->getAddBy()->getName()],			"path"      => $oldPath,			"extention" => $file->getExtention(),			"name"      => $file->getName(),			"desc"      => $file->getDesc(),			"assess"    => $file->getAssess(),		];	}	public function singleDeleteAction(Request $request, $fileGroup, $fileCode)	{		$em = $this->getDoctrine()->getManager();		$file = $em->getRepository('NetlivaFileBundle:FileList')->findOneBy(["group"=>$fileGroup,"code"=>$fileCode]);		$inPast = $file->getInPast();		$user = $this->container->get('security.token_storage')->getToken()->getUser();		$inPast[] = $this->createPastStructure($file, $this->moveToOld($file->getPath()));		$name = $file->getName();		$file->setUpdateAt(new \DateTime());		$file->setName($name);		$file->setDesc(null);		$file->setPath(null);		$file->setInPast($inPast);		$file->setExtention(null);		$file->setUpdateBy($user);		$file->setAssess(null);		$em->persist($file);		$em->flush();		$cacheService = $this->get("cache_service");		$cacheService->clearCache("fileUploader", ["fileGroup"=>$fileGroup, "fileCode"=>$fileCode]);		return new JsonResponse(array('status' => "success", "fileGroup"=>$fileGroup, "fileCode"=>$fileCode));	}	public function deleteAction($id)	{		$em = $this->getDoctrine()->getManager();		$file = $em->getRepository('NetlivaFileBundle:FileList')->find($id);		if (!$file)			return new JsonResponse(array('status' => "error"));		$em->remove($file);		$em->flush();		return new JsonResponse(array('status' => "success"));	}	public function assessAction(Request $request)	{		$fileId    = $request->request->get("fileId");		$operation = $request->request->get("operation");		$em        = $this->getDoctrine()->getManager();		$file      = $em->getRepository('NetlivaFileBundle:FileList')->find($fileId);		$inPast    = $file->getInPast();		$user      = $this->container->get('security.token_storage')->getToken()->getUser();		if ($operation == "rejection")		{			$inPast[]  = $this->createPastStructure($file, $this->moveToOld($file->getPath()));			$description = $request->request->get("description");			$name = $file->getName();			$file->setUpdateAt(new \DateTime());			$file->setName($name);			$file->setDesc($description);			$file->setPath(null);			$file->setInPast($inPast);			$file->setExtention(null);			$file->setUpdateBy($user);		}		$file->setAssess($operation);		$em->persist($file);		$em->flush();		$cacheService = $this->get("cache_service");		$cacheService->clearCache("fileUploader", ["fileGroup"=>$file->getGroup(), "fileCode"=>$file->getCode()]);		return new JsonResponse(array('status' => "success", "fileGroup"=>$file->getGroup(), "fileCode"=>$file->getCode()));	}	public function allApproveAction(Request $request)	{		$em        = $this->getDoctrine()->getManager();		$fileGroup = $request->request->get("fileGroup");		$operation = $request->request->get("operation");		$qb = $em->getRepository('NetlivaFileBundle:FileList')->createQueryBuilder("fl");		$qb->where(			$qb->expr()->andX(				$qb->expr()->eq("fl.group", ":fl_grb"),				$qb->expr()->isNull("fl.assess")			)		);		$qb->setParameter("fl_grb", $fileGroup);		$files = $qb->getQuery()->getResult();		$cacheService = $this->get("cache_service");		foreach ($files as $file)		{			$file->setAssess($operation);			$cacheService->clearCache("fileUploader", ["fileGroup"=>$file->getGroup()]);		}		$em->flush();		return new JsonResponse(array('status' => "success", "fileGroup"=>$fileGroup));	}	public function singleUploadAction(Request $request)	{		$options   = json_decode($request->get('opt'), true);		if (!is_array($options)) $options = [];		$listId     = $request->get('listId');		$fileList   = $listId ? $this->get('service_container')->getParameter('netliva_filehelper.file_list') : null;		$fileConf   = $this->get('service_container')->getParameter('netliva_filehelper.config');		$uplFiles   = $request->files->get('singleFile');		$fileGroup  = $request->get('fileGroup');		$postedCode = $request->get('fileCode');		$name       = $request->get('name');		$rename     = key_exists('rename',$options) && $options["rename"];		$subDir     = key_exists('subDir',$options) ? $options["subDir"] : null;		$uploadeds    = [];		$subDir       = $subDir ? "categorized/".$subDir : "files/".$fileGroup;		$resp_status  = null;		$resp_message = [];		$total = 0;		$success = 0;		$max_upload = min(ini_get('post_max_size'), ini_get('upload_max_filesize'));		$max_upload = str_replace('M', '', $max_upload);		$max_upload = $max_upload * 1024 *1024;		if (!$max_upload) $max_upload = $fileConf["max_size"];		if ($uplFiles instanceof UploadedFile) $uplFiles = [$uplFiles];		if (is_array($uplFiles))		{			foreach ($uplFiles as $uplFile)			{				$fileCode  = $postedCode;				$nameArray = [$name];				$status    = 'success';				$message   = 'Kayıt Başarlı';				if (($uplFile instanceof UploadedFile) && ($uplFile->getError() == '0'))				{					if ($uplFile->getSize() < $max_upload)					{						$selectedStack = null;						if (key_exists("hard_stack_list", $options) and count($options["hard_stack_list"]))						{							$selectedStack = $options["hard_stack_list"][$name];							$selectedStack["key"] = $name;							$name = $selectedStack["name"];							$nameArray[] = $name;							if (!$fileCode)								$fileCode = $selectedStack["key"];						}						$originalName = $uplFile->getClientOriginalName();						$name_array = explode('.', $originalName);						$file_type = strtolower($name_array[sizeof($name_array) - 1]);						if (in_array($file_type, $fileConf["valid_filetypes"]))						{							if (!$name)							{								$name = $fileGroup."-".$fileCode;								if ($fileList)								{									$flTemp = $fileList[$listId]["values"];									foreach (explode("_", $fileCode) as $code)									{										if (key_exists($code, $flTemp))										{											$name = $flTemp[$code]["name"];											if (key_exists("children", $flTemp[$code]) && count($flTemp[$code]["children"]))												$flTemp = $flTemp[$code]["children"];											else break;										}									}								}							}							$em = $this->getDoctrine()->getManager();							$oldPath = null;							if (!$fileCode)							{								$fileCode = $em->createQueryBuilder()									->select('MAX(CAST(f.code as INTEGER))')									->from('NetlivaFileBundle:FileList', 'f')									->where('f.group = :grp')									->setParameter("grp",$fileGroup)									->getQuery()									->getSingleScalarResult();								$fileCode = $fileCode ? $fileCode+1 : 1;								$file = false;							}							else {								$file = $em->getRepository('NetlivaFileBundle:FileList')->findOneBy(["group"=>$fileGroup,"code"=>$fileCode]);								if ($file)									$oldPath = $this->moveToOld($file->getPath());							}							//Start Uploading File							$document = new Document();							$document->setFile($uplFile);							$document->setUploadDirectory($fileConf["upload_path"]);							$document->setSubDirectory($subDir);							if	($rename and mb_strlen($rename)>5) $document->setNewName($rename);							else if ($rename and $name) $document->setNewName($name);							$document->processFile();							$path = $document->getRelativePath();							$nameArray[]=$name;							$date = is_array($options) && array_key_exists("selectDate",$options) && $options["selectDate"] ? new \DateTime($request->get('file_date')) : new \DateTime();							$user = $this->container->get('security.context')->getToken()->getUser();							if ($file)							{								if ($name == $fileGroup."-".$fileCode) $name = $file->getName();								$nameArray[]=$name;								$inPast = $file->getInPast();								$inPast[] = $this->createPastStructure($file, $oldPath);								$file->setUpdateAt($date);								$file->setUpdateBy($user);								$file->setInPast($inPast);							}							else							{								$file = new FileList();								$file->setAddAt($date);								$file->setAddBy($user);								$file->setGroup($fileGroup);								$file->setCode($fileCode);							}							$file->setName($name);							$file->setDesc(null);							$file->setPath($path);							$file->setExtention($file_type);							$file->setAssess(null);							$em->persist($file);							$em->flush();							$cacheService = $this->get("cache_service");							$cacheService->clearCache("fileUploader", ["fileGroup"=>$fileGroup, "fileCode"=>$fileCode]);							$uploadeds[] = ['path' => $path, "fileCode"=>$fileCode, "fileName"=>$nameArray];						}						else						{							$status = 'failed';							$message = 'Uygun Olmayan Dosya Uzantısı ('.$file_type.')';						}					}					else					{						$status = 'failed';						$message = ($uplFile->getSize()/1048576) .' MB olan dosyanız izin verilen dosya yükleme boyutundan yüksektir. (Maks:'.($max_upload/1048576).' MB)';					}				}				else				{					$status = 'failed';					$message = 'File Error : '.($uplFile instanceof UploadedFile ? $uplFile->getError():"");				}				$total++;				if ($status == 'success')					$success++;				else					$resp_message[] = $message;				if (!$resp_status)					$resp_status = $status;				else if ($status != $resp_status)					$resp_status = "partial_success";			}		}		$last_error = error_get_last();		if ($last_error and is_array($last_error) and $last_error["type"] === 2) return new Response("");		return new JsonResponse(			[				'status'         => $resp_status,				'total'          => $total,				'success'        => $success,				'messages'       => $resp_message,				"fileGroup"      => $fileGroup,				"fileCode"       => $fileCode,				"uploaded_files" => $uploadeds			]		);	}}