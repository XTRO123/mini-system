<?php

namespace Mini\Plugin;

use Mini\Filesystem\FileNotFoundException;
use Mini\Filesystem\Filesystem;
use Mini\Foundation\Application;
use Mini\Plugin\Repository;
use Mini\Support\Collection;
use Mini\Support\Str;


class PluginManager
{
	/**
	 * @var \Mini\Foundation\Application
	 */
	protected $app;

	/**
	 * @var \Mini\Plugin\Repository
	 */
	protected $repository;


	/**
	 * Create a new Plugin Manager instance.
	 *
	 * @param Application $app
	 */
	public function __construct(Application $app, Repository $repository)
	{
		$this->app = $app;

		$this->repository = $repository;
	}

	/**
	 * Register the plugin service provider file from all plugins.
	 *
	 * @return mixed
	 */
	public function register()
	{
		$plugins = $this->repository->all();

		$plugins->each(function($properties)
		{
			$this->registerServiceProvider($properties);

			// The Assets Namespace will be registered only for Themes.
			//$this->registerAssetsNamespace($properties);
		});
	}

	/**
	 * Register the Plugin Service Provider.
	 *
	 * @param array $properties
	 *
	 * @return void
	 *
	 * @throws \Mini\Plugin\FileMissingException
	 */
	protected function registerServiceProvider($properties)
	{
		$basename = $properties['basename'];

		$namespace = $this->resolveNamespace($properties);

		// Calculate the name of Service Provider, including the namespace.
		$serviceProvider = "{$namespace}\\Providers\\PluginServiceProvider";

		$classicProvider = "{$namespace}\\{$basename}ServiceProvider";

		if (class_exists($serviceProvider)) {
			$this->app->register($serviceProvider);
		} else if (class_exists($classicProvider)) {
			$this->app->register($classicProvider);
		}
	}

	/**
	 * Register the Module Service Provider.
	 *
	 * @param string $properties
	 *
	 * @return string
	 */
	protected function registerAssetsNamespace($properties)
	{
		if ($properties['theme'] === false) return;

		//
		$directory = 'assets';

		if ($properties['location'] === 'local') {
			$directory = ucfirst($directory);
		}

		$path = $properties['path'] .$directory;

		if ($this->app['files']->isDirectory($path)) {
			$this->app['assets']->addNamespace($properties['slug'], $path);
		}
	}

	/**
	 * Resolve the correct Plugin namespace.
	 *
	 * @param array $properties
	 */
	public function resolveNamespace($properties)
	{
		if (isset($properties['namespace'])) {
			return $properties['namespace'];
		}

		return Str::studly($properties['slug']);
	}

	/**
	 * Resolve the correct plugin files path.
	 *
	 * @param array $properties
	 *
	 * @return string
	 */
	public function resolveClassPath($properties)
	{
		$path = $properties['path'];

		if ($properties['location'] === 'local') {
			return $path;
		}

		return $path .'src' .DS;
	}

	/**
	 * Resolve the correct module files path.
	 *
	 * @param array  $properties
	 * @param string $path
	 *
	 * @return string
	 */
	public function resolveAssetPath($properties, $path)
	{
		$directory = 'assets';

		if ($properties['location'] === 'local') {
			$directory = ucfirst($directory);
		}

		$basePath = $properties['path'] .$directory;

		return $basePath .DS .str_replace('/', DS, $path);
	}

	/**
	 * Dynamically pass methods to the repository.
	 *
	 * @param string $method
	 * @param mixed  $arguments
	 *
	 * @return mixed
	 */
	public function __call($method, $arguments)
	{
		return call_user_func_array(array($this->repository, $method), $arguments);
	}
}
