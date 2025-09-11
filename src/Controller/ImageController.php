<?php

namespace Netliva\SymfonyFileHelperBundle\Controller;

use Netliva\SymfonyFileHelperBundle\Models\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class ImageController extends AbstractController {

	public function __construct(
		private readonly ParameterBagInterface $parameterBag
	) {}

	public function singleUploadAction(Request $request) {
		$image       = $request->files->get('singleImg');
		$options     = json_decode($request->get("options"));
		$fileConf    = $this->parameterBag->get('netliva_filehelper.config');
		$status      = 'success';
		$relativePath= '';
		$message     = '';
		if (($image instanceof UploadedFile) && ($image->getError() == '0')) {
			if (($image->getSize() < 1024 * 1024 * 1024)) {
				$originalName = $image->getClientOriginalName();
				$name_array = explode('.', $originalName);
				$file_type = strtolower($name_array[count($name_array) - 1]);
				$valid_filetypes = array('jpg', 'jpeg', 'gif', 'bmp', 'png');
				if (in_array($file_type, $valid_filetypes))
				{
					$subDir = $options->subDirectory ? "categorized/".$options->subDirectory : "files/images";
					//Start Uploading File
					$document = new Document();
					$document->setUploadDirectory($fileConf["upload_path"]);
					$document->setSubDirectory($subDir);
					$document->setFile($image);
					$document->setOverwrite($options->overwrite ?? true);
					if ($options->newName) $document->setNewName($options->newName);
					$document->processFile();
					$relativePath = $document->getRelativePath();

				} else {
					$status = 'failed';
					$message = 'Invalid File Type';
				}
			} else {
				$status = 'failed';
				$message = 'Size exceeds limit';
			}
		} else {
			$status = 'failed';
			$message = 'File Error';
		}

		return new JsonResponse([
			'status'  => $status,
			'message' => $message,
			'path'    => $relativePath,
		]);
	}

}
