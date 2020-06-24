<?php

namespace Netliva\SymfonyFileHelperBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * A TWIG Extension which allows to show Controller and Action name in a TWIG view.
 *
 * The Controller/Action name will be shown in lowercase. For example: 'default' or 'index'
 *
 */
class NetlivaImageHelper extends AbstractExtension
{
    /**
     * @var Request
     */
    protected $request;


	private   $container;
	/**
	 * @var Environment
	 */
	private $twig;

	public function __construct(ContainerInterface $container, Environment $twig){
		$this->container = $container;
		$this->twig      = $twig;
	}

    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }


    public function getFunctions()
    {
        return array(
            new TwigFunction('get_single_image_uploader', [$this, 'getSingleImageUploader'], array('is_safe' => array('html'))),
        );
    }



    /**
    * Get current controller name
    */
    public function getSingleImageUploader($id, $options)
    {
		$default = [
			"resize"       => false,
			"thumb"        => [],
			"label"        => "Dosya Seçin",
			"subDirectory" => null,
			"newName"      => null,
			"overwrite"    => true
		];
		$options = array_merge($default, $options);

        return $this->twig->render('@NetlivaSymfonyFileHelper/imageUploader.html.twig', ["id"=>$id, "options"=>$options]);
    }

    public function getName()
    {
        return 'netliva_file_single_image_extension';
    }
}
