<?php

namespace XdebugTrace;

use Nette;
use Nette\Utils\Html;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class HtmlDumper extends Nette\Object
{

	/**
	 * @var StackTrace
	 */
	private $trace;



	/**
	 * @param StackTrace $trace
	 */
	public function __construct(StackTrace $trace)
	{
		$this->trace = $trace;
	}



	/**
	 * @param string $file
	 */
	public function dump($file)
	{
		echo "dumping into $file ...\n";
		$template = $this->createTemplate(__DIR__ . '/../template/trace.latte');
		$template->analyser = new TraceAnalyser($this->trace);
		$template->trace = $this->trace;
		$template->dumper = $this;
		$template->save($file);
	}



	/**
	 * @param string $file
	 * @return \Nette\Templating\FileTemplate|\stdClass
	 */
	private function createTemplate($file = NULL)
	{
		$template = new Nette\Templating\FileTemplate($file);
		$template->registerFilter(new Nette\Latte\Engine());
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');
		$template->registerHelperLoader(function ($name) {
			$method = '\XdebugTrace\HtmlDumper::' . $name;
			return is_callable($method) ? callback($method) : NULL;
		});
		$template->setCacheStorage(new \Nette\Caching\Storages\MemoryStorage());
		return $template;
	}



	/**
	 * @param TraceCall $call
	 *
	 * @return int
	 */
	public static function callOffset(TraceCall $call)
	{
		if (($lvl = $call->getRelativeLevel()) > 1) {
			return ($lvl - 1) * 10;
		}

		return 0;
	}



	/**
	 * @param TraceCall $call
	 *
	 * @return Html
	 */
	public static function callFile(TraceCall $call)
	{
		if (!$call->file || !$call->line) {
			return Html::el('span');
		}

		/** @var Html $link */
		$link = Nette\Utils\Html::el('a')
			->href(strtr(Nette\Diagnostics\Debugger::$editor, array(
			'%file' => rawurlencode($call->file),
			'%line' => $call->line
		)))
			->title("$call->file:$call->line");

		$link->setHtml(
			'...' . DIRECTORY_SEPARATOR . htmlSpecialChars(basename(dirname($call->file))) .
				DIRECTORY_SEPARATOR . '<b>' . htmlSpecialChars(basename($call->file)) . '</b>' .
				':' . $call->line
		);

		return $link;
	}



	/**
	 * @param integer $bytes
	 * @param int $precision
	 * @return string
	 */
	public static function formatBytes($bytes, $precision = 2)
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}



	/**
	 * @param float $time
	 * @param int $precision
	 *
	 * @return string
	 */
	public static function formatTime($time, $precision = 0)
	{
		if ($time === 0) {
			return '?';
		}

		$units = 's';
		if ($time < 0.000001) { // <1us
			$units = 'ns';
			$time *= 1000000000;

		} elseif ($time < 0.001) { // <1ms
			$units = "\xc2\xb5s";
			$time *= 1000000;

		} elseif ($time < 1) { // <1s
			$units = 'ms';
			$time *= 1000;
		}

		return round($time, $precision) . ' ' . $units;
	}



	/**
	 * Template helper converts seconds to HTML class.
	 *
	 * @param  float time interval in seconds
	 * @param  float over this value is interval classified as slow
	 * @param  float under this value is interval classified as fast
	 *
	 * @return string
	 */
	public static function timeClass($time, $slow = 0.02, $fast = 0.001)
	{
		if ($time === 0) {
			return '';
		}

		if ($time <= $fast) {
			return 'timeFast';

		} elseif ($time <= $slow) {
			return 'timeMedian';
		}

		return 'timeSlow';
	}

}
