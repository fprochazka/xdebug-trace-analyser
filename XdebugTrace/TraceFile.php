<?php

namespace XdebugTrace;

use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TraceFile extends Nette\Object
{

	/**
	 * @var string
	 */
	private $traceFile;

	/**
	 * @var resource
	 */
	private $stream;



	/**
	 * @param string $traceFile
	 */
	public function __construct($traceFile)
	{
		$this->traceFile = $traceFile;
	}



	/**
	 * @throws \Nette\IOException
	 * @throws \Nette\InvalidArgumentException
	 * @return \XdebugTrace\StackTrace
	 */
	public function createStackTrace()
	{
		@set_time_limit(0); // intentionally @

		$size = filesize($this->traceFile);
		if (!$this->stream = fopen($this->traceFile, 'r')) {
			throw new Nette\IOException("Can't open '$this->traceFile'");
		}

		$header1 = fgets($this->stream);
		$header2 = fgets($this->stream);
		if (!preg_match('~Version: 2.*~', $header1) || !preg_match('~File format: 2~', $header2)) {
			throw new \Nette\InvalidArgumentException("This file is not an Xdebug trace file made with format option '1'.");
		}

		$progress = new \progressbar();
		$completed = 0;

		$trace = new StackTrace();
		while (!feof($this->stream)) {
			$line = fgets($this->stream);
			$completed += strlen($line);

			if ($this->skipLine($line)) {
				continue;
			}

			$line = Strings::split($line, '~\t~');
			if (count($line) < 5) {
				dump(array('skipping' => $line));
				continue;
			}

			list($level, $id, $direction, $time, $memory) = $line;
			$call = new TraceCall($id, $level, $time, $memory, (int)$direction);

			if ((int)$direction === TraceCall::IN) {
				dump(array(count($line) => $line));
				list(,,,,, $call->function, $call->internalFunction, $call->includedFile, $call->file, $call->line) = $line;
			}

			$trace->insert($call);


		}

		fclose($this->stream);
		return $trace;
	}



	/**
	 * @param $line
	 * @return bool
	 */
	private function skipLine($line)
	{
		return preg_match('~^Version: (.*)~', $line)
			|| preg_match('~^File format: (.*)~', $line)
			|| preg_match('~^TRACE.*~', $line); // todo: just ignore for now..
	}

}
