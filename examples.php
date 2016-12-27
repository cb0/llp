<?php
/**
This file is part of llp.

llp is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

llp is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with llp.  If not, see <http://www.gnu.org/licenses/>.
 */
include_once("./llp.php");

//$res = new lisp(new plus, 10, new lisp(new plus, 5, new lisp(new mul, 2)));
//$res = new lisp(new plus(), 10);
//$tmp = $res->ev();
//var_dump($tmp);


//$xx = l(new plus, 10, 2, l(new mul, 2, 2));
//var_dump($xx);

$xx = l('+',
        10,
        l('*',
          2, 2),
        l('/', 4, 2));

$xx = l('+', l('/', 4, 2), 2);
$xx = l('*', l('-', 2), -1);

$xx = l('==', 10, 3);

$xx = l('if', l(">", 10, 5), l('+', 2, 3), l('-', 5));

$xx = l(
    'progn',
    l('+', 2, l('*', 10, 10)),
    l('-', 5)
);


$xx = l(
    'progn',
    l('def', 'x', 3),
    l('def', 'y', 2),
    l('def', 'x', l('+', 'x', 'y')),
    l('def', 'x', l('*', 2, 'x')),
    l('-', 'x')
);


//list in php
$xx = l("!", l(">", 10, 3));

$xx = l(
    'progn',
    l(
        'def',
        'result',
        l(
            'if',
            l(
                "not",
                l(">", 10, 5)
            ),
            l('+', 2, 3),
            l('-', l('*', 10, 10))
        )
    ),
    l('print', 'result')
);

l("print", "Hello World!");

//var_dump($xx);

