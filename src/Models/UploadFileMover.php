<?php

namespace Netliva\SymfonyFileHelperBundle\Models;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

use Symfony\Component\HttpFoundation\File\File;

/**
 * Description of UploadFileMover
 *
 * @author Manoj
 */
class UploadFileMover
{
    /** @var File */
    private $file;
    private $uploadBasePath;
    private $relativePath = null;
    private $overwrite = null;
    private $newName = null;

    private function normalize ($string)
    {
        $string = preg_replace('/\s+/', "_", $string);
        $string = preg_replace("/[^a-zA-Z0-9_\-\.üğşçöıÜĞİŞÇÖ]/", "_", $string);
        $string = preg_replace('/_+/', "_", $string);

        return $string;
    }

    public function moveUploadedFile()
    {
        if	(!$this->getFile() instanceof File) {
            throw new \Exception("Upload dosyası doğru şekilde gönderilmedi");
        }
        if	(!$this->getUploadBasePath()) {
            throw new \Exception("Upload klasörü belirlenmedi");
        }

        $ext = $this->getFile()->getClientOriginalExtension();
        $name = $this->getFile()->getClientOriginalName();
        if	($this->getNewName())
        {
            $name = $this->getNewName();
            if	($ext) {
                $name .= ".".$ext;
            }
        }
        $name = $this->normalize($name);

        $targetDir = $this->getUploadBasePath();
        if	($this->getRelativePath())
        {
            $targetDir .= DIRECTORY_SEPARATOR . $this->getRelativePath();
        }

        if (!is_dir($targetDir))
            mkdir($targetDir, 0777, true);

        if	($this->overwrite)
        {
            if	(file_exists($targetDir.DIRECTORY_SEPARATOR.$name)) {
                unlink($targetDir.DIRECTORY_SEPARATOR.$name);
            }
        }
        else
        {
            $i=1;
            while (file_exists($targetDir.DIRECTORY_SEPARATOR.$name))
            {
                $prev = $i == 1 ? "" : "(".$i. ")";
                $i++;
                $next =  "(".$i. ")";
                if ($ext)
                {
                    $prev .= "." . $ext;
                    $next .= "." . $ext;
                }
                $name = str_replace($prev,  $next, $name);
            }
        }

        $this->getFile()->move($targetDir, $name);

        return $name;
    }

    /**
     * @return File
     */
    public function getFile (): File
    {
        return $this->file;
    }

    /**
     * @param File $file
     */
    public function setFile (File $file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getUploadBasePath ()
    {
        return $this->uploadBasePath;
    }

    /**
     * @param mixed $uploadBasePath
     */
    public function setUploadBasePath ($uploadBasePath)
    {
        $this->uploadBasePath = $uploadBasePath;
    }

    /**
     * @return mixed
     */
    public function getRelativePath ()
    {
        return $this->relativePath;
    }

    /**
     * @param mixed $relativePath
     */
    public function setRelativePath ($relativePath)
    {
        $this->relativePath = $relativePath;
    }

    /**
     * @return mixed
     */
    public function getNewName ()
    {
        return $this->newName;
    }

    /**
     * @param mixed $newName
     */
    public function setNewName ($newName)
    {
        $this->newName = $newName;
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


}

?>
