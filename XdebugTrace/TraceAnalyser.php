<?php

namespace XdebugTrace;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TraceAnalyser extends Nette\Object
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

}
