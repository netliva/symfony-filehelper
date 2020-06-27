<?php
namespace Netliva\SymfonyFileHelperBundle\Event;


final class NetlivaFileHelperEvents
{
	/**
	 * Güvenlikli Medya URL'si Oluşturulurken Çağrılır
	 *
	 * @Event("Netliva\SymfonyFileHelperBundle\Event\SecuredUrlEvent")
	 */
	const  SECURED_URL = 'netliva_filehelper.secured_url';

	/**
	 * Genel Medya URL'si Oluşturulurken Çağrılır
	 *
	 * @Event(Netliva\SymfonyFileHelperBundle\Event\PublicUrlEvent")
	 */
	const  PUBLIC_URL = 'netliva_filehelper.public_url';

}
