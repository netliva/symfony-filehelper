<?php
namespace Netliva\SymfonyFileHelperBundle;

use Netliva\SymfonyFileHelperBundle\DependencyInjection\NetlivaSymfonyFileHelperExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetlivaSymfonyFileHelperBundle extends Bundle
{
	public function getContainerExtension ()
	{
		if (null === $this->extension)
		{
			$this->extension = new NetlivaSymfonyFileHelperExtension();
		}
		return $this->extension;
	}
}
