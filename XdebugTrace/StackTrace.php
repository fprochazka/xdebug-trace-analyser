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
			$call = $opener;

		} else {
			$this->registry[$call->id] = $call;
		}

		if ($this->last && !$call->getParent()) {
			if ($this->last->level < $call->level) {
				$call->setParent($this->last);

			} elseif ($this->last->level > $call->level) {
				if ($parent = $this->last->getParent()) {
					$call->setParent($parent->getParent());
				}

			} else {
				$call->setParent($this->last->getParent());
			}
		}

		if (!$call->getParent() && !$opener) {
			$this->calls[] = $call;
		}

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
