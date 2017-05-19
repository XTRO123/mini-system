<?php

namespace Mini\Foundation\Providers;

use Mini\Support\ServiceProvider;
use Mini\Foundation\ConfigPublisher;
use Mini\Foundation\Console\ConfigPublishCommand;


class PublisherServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerConfigPublisher();

		$this->commands('command.config.publish');
	}

	/**
	 * Register the configuration publisher class and command.
	 *
	 * @return void
	 */
	protected function registerConfigPublisher()
	{
		$this->registerConfigPublishCommand();

		$this->app->bindShared('config.publisher', function($app)
		{
			$path = $app['path'] .DS .'Config';

			$publisher = new ConfigPublisher($app['files'], $app['config'], $path);

			return $publisher;
		});
	}

	/**
	 * Register the configuration publish console command.
	 *
	 * @return void
	 */
	protected function registerConfigPublishCommand()
	{
		$this->app->bindShared('command.config.publish', function($app)
		{
			$configPublisher = $app['config.publisher'];

			return new ConfigPublishCommand($configPublisher);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('config.publisher', 'command.config.publish');
	}

}
