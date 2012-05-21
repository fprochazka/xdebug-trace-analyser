<?php

namespace XdebugTrace;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method \XdebugTrace\TraceCall current()
 */
class RecursiveCallIterator extends \RecursiveArrayIterator implements \Countable
{

	/**
	 * @return bool
	 */
	public function hasChildren()
	{
		return TRUE;
	}



	/**
	 * The sub-iterator for the current element.
	 * @return \RecursiveIterator
	 */
	public function getChildren()
	{
		return $this->current()->getIterator();
	}



	/**
	 * Returns the count of elements.
	 * @return int
	 */
	public function count()
	{
		return iterator_count($this);
	}

}
