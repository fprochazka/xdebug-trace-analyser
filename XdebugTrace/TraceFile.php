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
		$filesize = filesize($this->traceFile);
		if (!$this->stream = fopen($this->traceFile, 'r')) {
			throw new Nette\IOException("Can't open '$this->traceFile'");
		}

		$header1 = fgets($this->stream);
		$header2 = fgets($this->stream);
		if (!preg_match('~Version: 2.*~', $header1) || !preg_match('~File format: 2~', $header2)) {
			throw new \Nette\InvalidArgumentException("This file is not an Xdebug trace file made with format option '1'.");
		}

		$progress = new \progressbar(100, 'Parsing file', function () {
			return '(using ' .number_format(memory_get_usage() / 1000000, 2, '.', ' ') . 'MB)';
		});

		$progress->update(0);
		$read = $linesCounter = 0;

		$trace = new StackTrace();
		while (!feof($this->stream)) {
			$line = fgets($this->stream);
			$read += strlen($line);
			$linesCounter += 1;

			if ($this->skipLine($line)) {
				continue;
			}

			$line = array_map('trim', Strings::split($line, '~\t~'));

			list($level, $id, $direction, $time, $memory) = $line;
			$call = new TraceCall($id, $level, $time, $memory, (int)$direction);

			if ((int)$direction === TraceCall::IN && count($line) === 10) {
				list(,,,,, $call->function, $call->internal, $call->includedFile, $call->file, $call->line) = $line;
				$call->internal = ($call->internal === "0");

				if (strcmp(substr($call->file, -13), "eval()'d code") === 0) {
					if ($evald = Strings::match($call->file, '~(?P<file>.*)\((?P<line>[0-9]+)\) : eval\(\)\'d code$~')) {
						$call->evalInfo = "- eval()'d code ($call->line)";
						$call->file = $evald['file'];
						$call->line = $evald['line'];
					}
				}

				if (strpos($call->function, '{closure:') === 0) {
					if ($evald = Strings::match($call->function, '~^{closure:(.*):([0-9]+)~')) {
						$call->function = '{closure}';
					}
					// {closure:/db.php:50-50}
				}
			}

			$trace->insert($call);

			if ($linesCounter % 1000 === 0) {
				$progress->update(($read/$filesize) * 100);
			}
		}

		$progress->update(100);
		echo "\n";

		fclose($this->stream);
		return $trace;
	}



	/**
	 * @param $line
	 * @return bool
	 */
	private function skipLine($line)
	{
		return trim($line) == ""
			|| preg_match('~^Version: (.*)~', $line)
			|| preg_match('~^File format: (.*)~', $line)
			|| preg_match('~^TRACE.*~', $line); // todo: just ignore for now..
	}

}
