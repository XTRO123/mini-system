<?php

namespace Mini\Foundation\Exceptions;

use Mini\Http\Response as HttpResponse;
use Mini\Foundation\Contracts\ExceptionHandlerInterface;
use Mini\Support\Facades\Config;
use Mini\Support\Facades\Response;
use Mini\View\View;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use Psr\Log\LoggerInterface;

use Exception;


class Handler implements ExceptionHandlerInterface
{
	/**
	 * The log implementation.
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $log;

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = array();

	/**
	 * Create a new exception handler instance.
	 *
	 * @param  \Psr\Log\LoggerInterface  $log
	 * @return void
	 */
	public function __construct(LoggerInterface $log)
	{
		$this->log = $log;
	}

	/**
	 * Report or log an exception.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
		if ($this->shouldReport($e)) {
			$this->log->error($e);
		}
	}

	/**
	 * Determine if the exception should be reported.
	 *
	 * @param  \Exception  $e
	 * @return bool
	 */
	public function shouldReport(Exception $e)
	{
		foreach ($this->dontReport as $type) {
			if ($e instanceof $type) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Render an exception into a response.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Mini\Http\Response
	 */
	public function render($request, Exception $e)
	{
		if ($e instanceof HttpException) {
			return $this->createResponse($this->renderHttpException($e), $e);
		}

		return $this->createResponse($this->convertExceptionToResponse($e), $e);
	}

	/**
	 * Map exception into an Mini-me response.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Response  $response
	 * @param  \Exception  $e
	 * @return \Mini\Http\Response
	 */
	protected function createResponse($response, Exception $e)
	{
		$response = new HttpResponse($response->getContent(), $response->getStatusCode(), $response->headers->all());

		$response->exception = $e;

		return $response;
	}

	/**
	 * Render the given HttpException.
	 *
	 * @param  \Symfony\Component\HttpKernel\Exception\HttpException  $e
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function renderHttpException(HttpException $e)
	{
		return $this->convertExceptionToResponse($e);
	}

	/**
	 * Convert the given exception into a Response instance.
	 *
	 * @param  \Exception  $e
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function convertExceptionToResponse(Exception $e)
	{
		$debug = Config::get('app.debug');

		//
		$e = FlattenException::create($e);

		$handler = new SymfonyExceptionHandler($debug);

		return SymfonyResponse::create($handler->getHtml($e), $e->getStatusCode(), $e->getHeaders());
	}

	/**
	 * Render an exception to the console.
	 *
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @param  \Exception  $e
	 * @return void
	 */
	public function renderForConsole($output, Exception $e)
	{
		with(new ConsoleApplication)->renderException($e, $output);
	}
}
