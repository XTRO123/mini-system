<?php

namespace Mini\Assets\Middleware;

use Mini\Foundation\Application;

use Symfony\Component\HttpKernel\Exception\HttpException;

use Closure;


class ServeAsset
{
	/**
	 * The application implementation.
	 *
	 * @var \Mini\Foundation\Application
	 */
	protected $app;

	/**
	 * Create a new middleware instance.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$assets = $this->app['assets.router'];

		return $assets->dispatch($request) ?: $next($request);
	}
}
