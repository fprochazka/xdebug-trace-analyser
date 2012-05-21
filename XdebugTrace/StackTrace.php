<?php

namespace XdebugTrace;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class StackTrace extends Nette\Object implements \IteratorAggregate
{

	/**
	 * @var TraceCall[]
	 */
	private $calls;

	/**
	 * @var TraceCall[]
	 */
	private $registry = array();

	/**
	 * @var TraceCall
	 */
	private $last;



	/**
	 * @param integer $id
	 * @return null|TraceCall
	 */
	public function get($id)
	{
		return isset($this->registry[$id]) ? $this->registry[$id] : NULL;
	}



	/**
	 * @param TraceCall $call
	 */
	public function insert(TraceCall $call)
	{
		if ($opener = $this->get($call->id)) {
			$opener->memoryOut = $call->memoryOut;
			$opener->timeOut = $call->timeOut;

		} else {
			$this->registry[$call->id] = $call;
		}

		// todo: hierarchy!

		$this->last = $call;
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->calls);
	}

}
