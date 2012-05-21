<?php


/**
 * @author Alex B. Snet
 * @see http://www.phpclasses.org/package/5020-PHP-Draw-a-progress-bar-in-ANSI-console.html
 */
class progressbar extends console
{

	private $escapeSequence = "\033[%sm";

	private $text = '';
	private $steps = 0;
	private $delim = '';
	private $step = 0;
	private $maxchars = 70;



	public function __construct($steps = 100, $text = '', $delim = '#', $maxchars = 70)
	{
		$this->steps = abs($steps);
		$this->step = 0;
		$this->text = $text;
		$this->delim = $delim;
		$this->maxchars = $maxchars;
		$this->draw();
	}



	public function update($step = NULL)
	{
		if ($step !== NULL) {
			$this->step = $step;

		} else {
			$this->step++;
		}

		$this->redraw();
	}



	private function draw()
	{
		print $this->text . ' [';

		$proc = round(($this->step / $this->steps) * 100, 0);
		$complete = $proc . '% complete';
		//$complete = sprintf($this->afterText,

		$isuse = strlen($complete) + 4 + strlen($this->text);

		$max = $this->maxchars - $isuse;

		$dash = round($max * ($proc / 100) + 1);
		$free = $max - $dash;

		//print 'max:'.$max.' dash:'.$dash.' free:'.$free;
		if ($dash > 0)
			print str_repeat($this->delim, $dash);
		if ($free > 0)
			print str_repeat('-', $free);
		print '] ' . $complete;
	}



	private function redraw()
	{
		$this->toPos();
		$this->draw();
	}



	private function toPos($column = 1)
	{
		echo "\033[{$column}G";
	}
}



/**
 * @author Alex B. Snet
 * @see http://www.phpclasses.org/package/4969-PHP-Control-output-of-text-to-an-ANSI-console.html
 */
class console
{

	const TARGET_OUTPUT = "php://output";
	const TARGET_STDOUT = "php://stdout";
	const TARGET_STDERR = "php://stderr";
	const TARGET_STDIN = "php://stdin";

	protected static $color = array(
		'gray' => 30,
		'black' => 30,
		'red' => 31,
		'green' => 32,
		'yellow' => 33,
		'blue' => 34,
		'magenta' => 35,
		'cyan' => 36,
		'white' => 37,
		'default' => 39
	);

	protected static $bgcolor = array(
		'gray' => 40,
		'black' => 40,
		'red' => 41,
		'green' => 42,
		'yellow' => 43,
		'blue' => 44,
		'magenta' => 45,
		'cyan' => 46,
		'white' => 47,
		'default' => 49,
	);

	protected static $style = array(
		'default' => '0',

		'bold' => 1,
		'faint' => 2,
		'normal' => 22,

		'italic' => 3,
		'notitalic' => 23,

		'underlined' => 4,
		'doubleunderlined' => 21,
		'notunderlined' => 24,

		'blink' => 5,
		'blinkfast' => 6,
		'noblink' => 25,

		'negative' => 7,
		'positive' => 27,
	);

	private $text = "";



	// Outputing
	public function draw($text = '')
	{
		echo $this->text . $text;
		$this->text = '';
		return $this;
	}



	// Input
	public function readNumeric()
	{
		$stdin = fopen('php://stdin', 'r');
		$line = trim(fgets($stdin));
		fscanf($stdin, "%d\n", $number);
		return $number;
	}



	public function readString()
	{
		$stdin = fopen('php://stdin', 'r');
		$line = trim(fgets($stdin));
		fscanf($stdin, "%s\n", $string);
		return $string;
	}



	// Sound
	public function beep()
	{
		echo "\007";
		return $this;
	}



	public function setSoundHerz($herz = 100)
	{
		echo "\033[10;{$herz}]";
		return $this;
	}



	public function setSoundLong($milliseconds = 500)
	{
		echo "'033[11;{$milliseconds}]";
		return $this;
	}



	// Cursor position
	public function toPos($row = 1, $column = 1)
	{
		echo "\033[{$row};{$column}H";
		return $this;
	}



	public function cursorUp($lines = 1)
	{
		echo "\033[{$lines}A";
		return $this;
	}



	public function cursorDown($lines = 1)
	{
		echo "\033[{$lines}B";
		return $this;
	}



	public function cursorRight($columns = 1)
	{
		echo "\033[{$columns}C";
		return $this;
	}



	public function cursorLeft($columns = 1)
	{
		echo "\033[{$columns}D";
		return $this;
	}



	// Text colors
	public function setStyle($style = 'default')
	{
		$this->text .= "\033[" . $this->style[$style] . "m";
		return $this;
	}



	public function setColor($color = 'default')
	{
		$this->text .= "\033[" . $this->color[$color];
		return $this;
	}



	public function setBgColor($color = 'default')
	{
		$this->text .= "\033[" . $this->bgcolor[$color];
		return $this;
	}



	// Application
	public function setAppName($name = '')
	{
		echo "\033]0;{$name}\007";
		return $this;
	}



	public function setTitle($name = '')
	{
		echo "\033]2;{$name}\007";
		return $this;
	}



	public function setIcon($name = '')
	{
		echo "\033]1;{$name}\007";
		return $this;
	}



	// Other
	public function clear()
	{
		echo "\033c";
		return $this;
	}



	public function console($num = 1)
	{
		echo "\033[12;{$num}]";
		return $this;
	}

}
