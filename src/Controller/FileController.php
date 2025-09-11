<?php



namespace Netliva\SymfonyFileHelperBundle\Controller;



use Doctrine\ORM\EntityManagerInterface;
use Netliva\SymfonyFileHelperBundle\Entity\FileList;
use Netliva\SymfonyFileHelperBundle\Entity\UploaderInterface;
use Netliva\SymfonyFileHelperBundle\Models\Document;
use Netliva\SymfonyFileHelperBundle\Services\NetlivaFileHelper;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class FileController extends AbstractController
{
    public function __construct (
        private readonly NetlivaFileHelper $netlivaFileHelper,
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ParameterBagInterface $parameterBag,
    ) { }

    public function refreshLineAction(Request $request) {

		$fileGroup = $request->get('fileGroup');
		$fileCode = $request->get('fileCode');
		$listId = $request->get('listId');
		$options = $request->get('options');

		if ($listId)
		{
			$fileInfo = $this->parameterBag->get('netliva_filehelper.file_list');
			$fileList = $fileInfo[$listId]["values"];
			foreach (explode("_", $fileCode) as $code)
			{
				if (array_key_exists($code, $fileList))
				{
					$fileInfo = $fileList[$code];
					if (array_key_exists("children", $fileInfo) && count($fileInfo["children"]))
						$fileList = $fileInfo["children"];
					else break;
				}
			}

			return $this->render('@NetlivaSymfonyFileHelper/List.hard.line.html.twig', [
				"group"    => $fileGroup,
				"listId"   => $listId,
				"value"    => $fileInfo,
				"key"      => $fileCode,
				"options"  => $options,
				"ajaxload" => true,
			]);
		}

		return new Response("Dosya Bulunamadı");

	}

	public function refreshSingleWithIconAction(Request $request) {

		$fileGroup = $request->get('fileGroup');
		$fileCode = $request->get('fileCode');
		$opt = json_decode($request->get('opt'));

		$file = $this->entityManager->getRepository(FileList::class)->findOneBy(["group"=>$fileGroup,"code"=>$fileCode]);

		return $this->render('@NetlivaSymfonyFileHelper/fileUploader.html.twig', [
			 "fileGroup" => $fileGroup,
			 "listId"    => null,
			 "fileCode"  => $fileCode,
			 "opt"       => $opt,
			 "file"      => $file,
		 ]);


	}

	public function refreshSoftListAction(Request $request)
	{
		$options = json_decode($request->get('opt'), true);
        return $this->render('@NetlivaSymfonyFileHelper/List.soft.lines.html.twig', $this->netlivaFileHelper->getSoftFileListDatas($options));
	}

	public function refreshStackAction (Request $request)
	{
		$fileGroup = $request->get('fileGroup');
		$uploadCode = $request->get('fileCode');
		$options = $request->get('options');
		$listId = $request->get('listId');

		[$fileGroup, $fileCode] = explode("-",$fileGroup);

		if ($listId)
		{
			$fileInfo = $this->parameterBag->get('netliva_filehelper.file_list');
			$fileList = $fileInfo[$listId]["values"];
			foreach (explode("_", $fileCode) as $code)
			{
				if (array_key_exists($code, $fileList))
				{
					$fileInfo = $fileList[$code];
					if (array_key_exists("children", $fileInfo) && count($fileInfo["children"]))
						$fileList = $fileInfo["children"];
					else break;
				}
			}

			return $this->render('@NetlivaSymfonyFileHelper/List.stack.html.twig', ["group"=>$fileGroup, "listId"=>$listId, "value"=>$fileInfo, "key" =>$fileCode, "options" =>$options, "ajaxload"=>true]);
		}

		return new Response("Dosya Bulunamadı");
	}

	public function moveToOld ($path)
	{
		$config = $this->parameterBag->get("netliva_filehelper.config");
		if ($path && file_exists($config["upload_path"].$path))
		{
			$exploded = explode("/", $path);

			$file_name = array_pop($exploded);
			$newdir = implode("/", $exploded).DIRECTORY_SEPARATOR.".old".DIRECTORY_SEPARATOR;
			if (!is_dir($config["upload_path"].$newdir)) mkdir($config["upload_path"].$newdir, 0777, true);

			$i=1;
			while (file_exists($config["upload_path"].$newdir.$file_name))
			{
				$prev = $i == 1 ? "" : "(".$i. ")";
				$i++;
				$next =  "(".$i. ")";
				if ($prev)
				{
					$file_name = str_replace($prev,  $next, $file_name);
				}
				else
				{
					$fNameExp = explode(".",$file_name);
					$ext = null;
					if (count($fNameExp)>1)
					{
						$ext = array_pop($fNameExp);
					}
					$file_name = implode(".", $fNameExp). $next. ($ext?".".$ext:"");
				}
			}

			rename($config["upload_path"].$path, $config["upload_path"].$newdir.$file_name);

			$path = $newdir.$file_name;
		}
		return $path;
	}

	public function createPastStructure (FileList $file, $oldPath)
	{
		$updateBy = $file->getUpdateBy();
		$addBy = $file->getAddBy();
		
		$updateByData = $updateBy ? [
			"id" => null, // ID bilgisi mevcut değil
			"name" => (string)$updateBy
		] : null;
		
		$addByData = $addBy ? [
			"id" => null, // ID bilgisi mevcut değil
			"name" => (string)$addBy
		] : null;
		
		return [
			"addAt"     => $file->getUpdateAt() ? $file->getUpdateAt() : $file->getAddAt(),
			"addBy"     => $updateByData ?: $addByData,
			"path"      => $oldPath,
			"extention" => $file->getExtention(),
			"name"      => $file->getName(),
			"desc"      => $file->getDesc(),
			"assess"    => $file->getAssess(),
		];
	}

	public function singleDeleteAction(Request $request, $fileGroup, $fileCode)
	{
		$file = $this->entityManager->getRepository(FileList::class)->findOneBy(["group"=>$fileGroup,"code"=>$fileCode]);
		$inPast = $file->getInPast();
		$user = $this->tokenStorage->getToken()->getUser();
		$inPast[] = $this->createPastStructure($file, $this->moveToOld($file->getPath()));

		$name = $file->getName();
		$file->setUpdateAt(new \DateTime());
		$file->setName($name);
		$file->setDesc(null);
		$file->setPath(null);
		$file->setInPast($inPast);
		$file->setExtention(null);
		$file->setUpdateBy($user);
		$file->setAssess(null);

		$this->entityManager->persist($file);
		$this->entityManager->flush();

		return new JsonResponse(array('status' => "success", "fileGroup"=>$fileGroup, "fileCode"=>$fileCode));

	}

	public function deleteAction($id)
	{
		$file = $this->entityManager->getRepository(FileList::class)->find($id);
		if (!$file)
			return new JsonResponse(array('status' => "error"));

		$this->entityManager->remove($file);
		$this->entityManager->flush();

		return new JsonResponse(array('status' => "success"));

	}

	public function assessAction(Request $request)
	{
		$fileId    = $request->request->get("fileId");
		$operation = $request->request->get("operation");
		$file      = $this->entityManager->getRepository(FileList::class)->find($fileId);
		$inPast    = $file->getInPast();
		$user      = $this->tokenStorage->getToken()->getUser();

		if ($operation == "rejection")
		{
			$inPast[]  = $this->createPastStructure($file, $this->moveToOld($file->getPath()));
			$description = $request->request->get("description");

			$name = $file->getName();
			$file->setUpdateAt(new \DateTime());
			$file->setName($name);
			$file->setDesc($description);
			$file->setPath(null);
			$file->setInPast($inPast);
			$file->setExtention(null);
			$file->setUpdateBy($user);
		}
		$file->setAssess($operation);

		$this->entityManager->persist($file);
		$this->entityManager->flush();

		return new JsonResponse(array('status' => "success", "fileGroup"=>$file->getGroup(), "fileCode"=>$file->getCode()));
	}

	public function allApproveAction(Request $request)
	{
		$fileGroup = $request->request->get("fileGroup");
		$operation = $request->request->get("operation");

		$qb = $this->entityManager->getRepository(FileList::class)->createQueryBuilder("fl");
		$qb->where(
			$qb->expr()->andX(
				$qb->expr()->eq("fl.group", ":fl_grb"),
				$qb->expr()->isNull("fl.assess")
			)
		);
		$qb->setParameter("fl_grb", $fileGroup);
		$files = $qb->getQuery()->getResult();


		foreach ($files as $file)
			$file->setAssess($operation);

		$this->entityManager->flush();

		return new JsonResponse(array('status' => "success", "fileGroup"=>$fileGroup));
	}



	public function singleUploadAction(Request $request)
	{
		$options   = json_decode($request->get('opt'), true);
		if (!is_array($options)) $options = [];
		$listId     = $request->get('listId');
		$fileList   = $listId ? $this->parameterBag->get('netliva_filehelper.file_list') : null;
		$fileConf   = $this->parameterBag->get('netliva_filehelper.config');
		$uplFiles   = $request->files->get('singleFile');
		$fileGroup  = $request->get('fileGroup');
		$postedCode = $request->get('fileCode');
		$name       = $request->get('name');
		$desc       = $request->get('name');
		$rename     = array_key_exists('rename',$options) ? $options["rename"] : null;
		$subDir     = array_key_exists('subDir',$options) ? $options["subDir"] : null;

        $uploadeds    = [];
		$subDir       = $subDir ? "categorized/".$subDir : "files/".$fileGroup;
		$resp_status  = null;
		$resp_message = [];
		$total = 0;
		$success = 0;

		$max_upload = min(ini_get('post_max_size'), ini_get('upload_max_filesize'));
		$max_upload = str_replace('M', '', $max_upload);
		$max_upload = $max_upload * 1024 *1024;

		if (!$max_upload) $max_upload = $fileConf["max_size"];

		if ($uplFiles instanceof UploadedFile) $uplFiles = [$uplFiles];

        $fileCode  = null;
		if (is_array($uplFiles))
		{
			foreach ($uplFiles as $uplFile)
			{
				$fileCode  = $postedCode;
				$nameArray = [$name];
				$status    = 'success';
				$message   = 'Kayıt Başarlı';

				if (($uplFile instanceof UploadedFile) && ($uplFile->getError() == '0'))
				{
					if ($uplFile->getSize() < $max_upload)
					{

						$selectedStack = null;
						if (array_key_exists("hard_stack_list", $options) and count($options["hard_stack_list"]))
						{
							$selectedStack = $options["hard_stack_list"][$name];
							$selectedStack["key"] = $name;
							$name = $selectedStack["name"];
                            $desc = $selectedStack["name"];
							$nameArray[] = $name;
							if (!$fileCode)
								$fileCode = $selectedStack["key"];

						}

						$originalName = $uplFile->getClientOriginalName();
						$name_array = explode('.', $originalName);
						$file_type = strtolower($name_array[count($name_array) - 1]);
						if (in_array($file_type, $fileConf["valid_filetypes"]))
						{
							if (!$name)
							{
								$name = $fileGroup."-".$fileCode;
                                $desc = $originalName;
								if ($fileList)
								{
									$flTemp = $fileList[$listId]["values"];
									foreach (explode("_", $fileCode) as $code)
									{
										if (array_key_exists($code, $flTemp))
										{
											$name = $flTemp[$code]["name"];
											$desc = $flTemp[$code]["name"];
											if (array_key_exists("children", $flTemp[$code]) && count($flTemp[$code]["children"]))
												$flTemp = $flTemp[$code]["children"];
											else break;
										}
									}
								}
							}

							$oldPath = null;
							if (!$fileCode)
							{
								$fileCode = $this->entityManager->createQueryBuilder()
									->select('MAX(CAST(f.code as UNSIGNED))')
									->from(FileList::class, 'f')
									->where('f.group = :grp')
									->setParameter("grp",$fileGroup)
									->getQuery()
									->getSingleScalarResult();

								$fileCode = $fileCode ? $fileCode+1 : 1;
								$file = null;
							}
							else {
								$file = $this->entityManager->getRepository(FileList::class)->findOneBy(["group"=>$fileGroup,"code"=>$fileCode]);
								if ($file)
									$oldPath = $this->moveToOld($file->getPath());
							}

							//Start Uploading File
							$document = new Document();
							$document->setFile($uplFile);
							$document->setUploadDirectory($fileConf["upload_path"]);
							$document->setSubDirectory($subDir);
							if	(!!$rename and ($rename == 'uuid' || $rename == 'guid')) $document->setNewName(Uuid::uuid4());
							else if	(!!$rename and mb_strlen($rename)>5) $document->setNewName($rename);
							else if (!!$rename and $name) $document->setNewName($name);
							$document->processFile();
							$path = $document->getRelativePath();

							$nameArray[]=$name;
							$date = is_array($options) && array_key_exists("selectDate",$options) && $options["selectDate"] ? new \DateTime($request->get('file_date')) : new \DateTime();
                            /** @var UploaderInterface $user */
							$user = $this->getUser();
							if ($file)
							{
								/** @var $file FileList */
								$nameArray[]=$desc;

								$inPast = $file->getInPast();
								$inPast[] = $this->createPastStructure($file, $oldPath);
								$file->setUpdateAt($date);
								$file->setUpdateBy($user);
								$file->setInPast($inPast);
							}
							else
							{
								$file = new FileList();
								$file->setAddAt($date);
								$file->setAddBy($user);
								$file->setGroup($fileGroup);
								$file->setCode($fileCode);
							}

							$file->setName($desc);
							$file->setDesc(null);
							$file->setPath($path);
							$file->setExtention($file_type);
							$file->setAssess(null);


							$this->entityManager->persist($file);
							$this->entityManager->flush();

							$uploadeds[] = ['path' => $path, "fileCode"=>$fileCode, "fileName"=>$nameArray];
						}
						else
						{
							$status = 'failed';
							$message = 'Uygun Olmayan Dosya Uzantısı ('.$file_type.')';
						}

					}
					else
					{
						$status = 'failed';
						$message = ($uplFile->getSize()/1048576) .' MB olan dosyanız izin verilen dosya yükleme boyutundan yüksektir. (Maks:'.($max_upload/1048576).' MB)';
					}

				}
				else
				{
					$status = 'failed';
					$message = 'File Error : '.($uplFile instanceof UploadedFile ? $uplFile->getError():"");
				}

				$total++;
				if ($status == 'success')
					$success++;
				else
					$resp_message[] = $message;

				if (!$resp_status)
					$resp_status = $status;
				else if ($status != $resp_status)
					$resp_status = "partial_success";
			}
		}

		$last_error = error_get_last();
		if ($last_error and is_array($last_error) and $last_error["type"] === 2) return new Response("");

		return new JsonResponse(
			[
				'status'         => $resp_status,
				'total'          => $total,
				'success'        => $success,
				'messages'       => $resp_message,
				"fileGroup"      => $fileGroup,
				"fileCode"       => $fileCode,
				"uploaded_files" => $uploadeds
			]
		);
	}


}

