<?php

namespace XdebugTrace;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TraceCall extends Nette\Object implements \IteratorAggregate
{

	const IN = 0;
	const OUT = 1;

	/**
	 * @var integer
	 */
	public $id;

	/**
	 * @var integer
	 */
	public $level;

	/**
	 * @var float
	 */
	public $timeIn;

	/**
	 * @var float
	 */
	public $timeOut;

	/**
	 * @var integer
	 */
	public $memoryIn;

	/**
	 * @var integer
	 */
	public $memoryOut;

	/**
	 * @var string
	 */
	public $function;

	/**
	 * @var bool
	 */
	public $internal = FALSE;

	/**
	 * @var string
	 */
	public $includedFile;

	/**
	 * @var string
	 */
	public $file;

	/**
	 * @var string
	 */
	public $line;

	/**
	 * @var string
	 */
	public $evalInfo;

	/**
	 * @var TraceCall
	 */
	private $parent;

	/**
	 * @var TraceCall[]
	 */
	private $children = array();

	/**
	 * @var StackTrace
	 */
	private $stack;



	/**
	 * @param integer $id
	 * @param integer $level
	 * @param float $time
	 * @param integer $memory
	 * @param int $direction
	 */
	public function __construct($id, $level, $time, $memory, $direction = self::IN)
	{
		$this->id = (int)$id;
		$this->level = (int)$level;

		if ($direction === self::IN) {
			$this->timeIn = (float)$time;
			$this->memoryIn = (float)$memory;

		} else {
			$this->timeOut = (float)$time;
			$this->memoryOut = (float)$memory;
		}
	}



	/**
	 * @return int
	 */
	public function getRelativeLevel()
	{
		$lowest = $this->stack->getLowestLevel();
		return $this->level - $lowest;
	}



	/**
	 * @return float
	 */
	public function getInclusiveTime()
	{
		return ($this->timeOut && $this->timeIn)
			? $this->timeOut - $this->timeIn
			: 0;
	}



	/**
	 * @return int
	 */
	public function getInclusiveMemory()
	{
		return ($this->memoryIn && $this->memoryOut)
			? $this->memoryIn - $this->memoryOut
			: 0;
	}



	/**
	 * @return int
	 */
	public function getExclusiveMemory()
	{
		if (!$inclusive = $this->getInclusiveMemory()) {
			return 0;
		}

		$childrenMemory = 0;
		foreach ($this->children as $call) {
			$childrenMemory += $call->getInclusiveMemory();
		}

		return $inclusive - $childrenMemory;
	}



	/**
	 * @param \XdebugTrace\StackTrace $stack
	 */
	public function setStackTrace(StackTrace $stack)
	{
		$this->stack = $stack;
	}



	/**
	 * @param \XdebugTrace\TraceCall $parent
	 */
	public function setParent(TraceCall $parent = NULL)
	{
		if ($this->parent = $parent) {
			$parent->addCall($this);
		}
	}



	/**
	 * @return \XdebugTrace\TraceCall
	 */
	public function getParent()
	{
		return $this->parent;
	}



	/**
	 * @param \XdebugTrace\TraceCall $call
	 */
	public function addCall(TraceCall $call)
	{
		$this->children[] = $call;
	}



	/**
	 * @return TraceCall[]
	 */
	public function getChildren()
	{
		return $this->children;
	}



	/**
	 * @return \XdebugTrace\RecursiveCallIterator|\XdebugTrace\TraceCall[]
	 */
	public function getIterator()
	{
		return new RecursiveCallIterator($this->children);
	}

}

