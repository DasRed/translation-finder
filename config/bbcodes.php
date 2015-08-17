<?php

return [
	'bbcodes' => [
		['regex' => '#<br[\s/]{0,}>#i', 'replacement' => '[br]'],
		['regex' => '#<strong(.*)>(.+)</strong>#isU', 'replacement' => '[b$1]$2[/b]'],
		['regex' => '#<b(.*)>(.+)</b>#isU', 'replacement' => '[b$1]$2[/b]'],
		['regex' => '#<em(.*)>(.+)</em>#isU', 'replacement' => '[i$1]$2[/i]'],
		['regex' => '#<u(.*)>(.+)</u>#isU', 'replacement' => '[u$1]$2[/u]'],

		['regex' => '#<h1(.*)>(.+)</h1>#isU', 'replacement' => '[h1$1]$2[/h1]'],
		['regex' => '#<h2(.*)>(.+)</h2>#isU', 'replacement' => '[h2$1]$2[/h2]'],
		['regex' => '#<h3(.*)>(.+)</h3>#isU', 'replacement' => '[h3$1]$2[/h3]'],
		['regex' => '#<h4(.*)>(.+)</h4>#isU', 'replacement' => '[h4$1]$2[/h4]'],
		['regex' => '#<h5(.*)>(.+)</h5>#isU', 'replacement' => '[h5$1]$2[/h5]'],
		['regex' => '#<h6(.*)>(.+)</h6>#isU', 'replacement' => '[h6$1]$2[/h6]'],

		['regex' => '#<p(.*)>(.+)</p>#isU', 'replacement' => '[p$1]$2[/p]'],

		['regex' => '#<a href="mailto:(.+)">(.+)</a>#isU', 'replacement' => '[email=$1]$2[/email]'],
		['regex' => '#<a href="(.+)">(.+)</a>#isU', 'replacement' => '[url=$1]$2[/url]'],
	]
];