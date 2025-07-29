<?php

namespace Netliva\SymfonyFileHelperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): \Symfony\Component\Config\Definition\Builder\TreeBuilder
    {
        $treeBuilder = new TreeBuilder('netliva_filehelper');
        $rootNode = $treeBuilder->getRootNode();



        $rootNode
			->children()
				->arrayNode('config')
					->addDefaultsIfNotSet()
					->children()
						->arrayNode('valid_filetypes')
							->defaultValue(array(
								"pdf","docx","xlsx","pptx","rar","zip","7z","bmp","gif","jpg","jpeg","png","tiff", "xps",
								"mpg", "mp2", "mpeg", "mpe", "mpv", "ogg", "mp4", "m4p", "m4v", "avi", "wmv", "mov", "qt",
							   ))
							->prototype('scalar')->end()
						->end()
						->scalarNode('upload_path')->defaultValue("media/upload")->end()
						->scalarNode('public_uri_prefix')->defaultValue("media/upload")->end()
						->scalarNode('secure_uri_prefix')->defaultValue("media/upload")->end()
						->scalarNode('max_size')->defaultValue(1024 * 1024 * 1024)->end()
					->end()
				->end()

				->arrayNode('file_lists')
					->requiresAtLeastOneElement()
					->useAttributeAsKey('name')
					->prototype('array')
						->children()
							->scalarNode('title')->end()
							->scalarNode('desc')->end()
							->arrayNode('values')
								->prototype('array')
									->children()
										->scalarNode('name')->end()
										->booleanNode('optional')->defaultValue(false)->end()
										->booleanNode('multiupload')->defaultValue(false)->end()
										->scalarNode('type')->defaultValue("item")->end()
										->arrayNode('accepted_filters')->defaultValue([])->prototype('scalar')->end()->end()
										->arrayNode('children')
											->prototype('array')
												->children()
													->scalarNode('name')->end()
													->booleanNode('optional')->defaultValue(false)->end()
													->booleanNode('multiupload')->defaultValue(false)->end()
													->scalarNode('type')->defaultValue("item")->end()
													->arrayNode('accepted_filters')->defaultValue([])->prototype('scalar')->end()->end()
												->end()
											->end()
										->end()
									->end()
								->end()
							->end()
						->end()
					->end()
				->end()

			->end()

		;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
