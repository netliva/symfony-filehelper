<?php

namespace Netliva\SymfonyFileHelperBundle\Controller;

use Netliva\SymfonyFileHelperBundle\Models\Document;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class ImageController extends Controller {

	public function singleUploadAction(Request $request) {
		$image       = $request->files->get('singleImg');
		$options     = json_decode($request->get("options"));
		$fileConf    = $this->get('service_container')->getParameter('netliva_filehelper.config');
		$status      = 'success';
		$uploadedURL = '';
		$message     = '';
		if (($image instanceof UploadedFile) && ($image->getError() == '0')) {
			if (($image->getSize() < 1024 * 1024 * 1024)) {
				$originalName = $image->getClientOriginalName();
				$name_array = explode('.', $originalName);
				$file_type = strtolower($name_array[sizeof($name_array) - 1]);
				$valid_filetypes = array('jpg', 'jpeg', 'gif', 'bmp', 'png');
				if (in_array($file_type, $valid_filetypes))
				{
					//Start Uploading File
					$document = new Document();
					$document->setUploadDirectory($fileConf["upload_path"]);
					$document->setSubDirectory($options->subDirectory?$options->subDirectory:'images');
					$document->setFile($image);
					$document->setOverwrite(isset($options->overwrite) ? $options->overwrite : true);
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
