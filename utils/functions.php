<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

use Nette\Callback;
use Nette\Diagnostics\Debugger;
use Nette\Diagnostics\Helpers;



/**
 * Outputs the variable content to file
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @param mixed $variable
 * @param int $maxDepth
 *
 * @return mixed
 */
function fd($variable, $maxDepth = 3)
{
	$style = <<<CSS
	pre.nette-dump { color: #444; background: white; }
	pre.nette-dump .php-array, pre.nette-dump .php-object { color: #C22; }
	pre.nette-dump .php-string { color: #080; }
	pre.nette-dump .php-int, pre.nette-dump .php-float { color: #37D; }
	pre.nette-dump .php-null, pre.nette-dump .php-bool { color: black; }
	pre.nette-dump .php-visibility { font-size: 85%; color: #999; }
CSS;

	$originalDepth = Debugger::$maxDepth;
	Debugger::$maxDepth = $maxDepth;
	$dump = "<pre class=\"nette-dump\">" . Nette\Diagnostics\Helpers::htmlDump($variable) . "</pre>\n";
	Debugger::$maxDepth = $originalDepth;
	$dump .= "<style>" . $style . "</style>";
	$file = Debugger::$logDirectory . '/dump_' . substr(md5($dump), 0, 6) . '.html';

	file_put_contents($file, $dump);
	if (Debugger::$browser) {
		exec(Debugger::$browser . ' ' . escapeshellarg($file));
	}

	return $variable;
}
