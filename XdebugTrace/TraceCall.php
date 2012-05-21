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
	public $internalFunction = FALSE;

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
	 * @var TraceCall
	 */
	private $parent;

	/**
	 * @var TraceCall[]
	 */
	private $children = array();



	/**
	 * @param integer $id
	 * @param integer $level
	 * @param float $time
	 * @param integer $memory
	 * @param int $direction
	 */
	public function __construct($id, $level, $time, $memory, $direction = self::IN)
	{
		$this->id = $id;
		$this->level = $level;

		if ($direction === self::IN) {
			$this->timeIn = $time;
			$this->memoryIn = $memory;

		} else {
			$this->timeOut = $time;
			$this->memoryOut = $memory;
		}
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

