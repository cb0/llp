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

/**
 * Wrapper for creating a lisp list object
 *
 * @return mixed|null
 */
function l()
{
    $l = new lisp();
    array_walk(func_get_args(), [$l, "addArg"]);

    return $l->ev();
}

/**
 * Class lisp "list" which basically holds all arguments and decides what operator to create.
 */
class lisp
{
    private $args = [];

    public function addArg($x)
    {
        if (is_string($x)) {
            switch ($x) {
                case "+":
                    $x = new plus;
                    break;
                case "-":
                    $x = new minus;
                    break;
                case "*":
                    $x = new multiplication;
                    break;
                case "/":
                    $x = new division;
                    break;
                case ">":
                    $x = new gt;
                    break;
                case ">=":
                    $x = new gte;
                    break;
                case "==":
                    $x = new comp;
                    break;
                case "!":
                case "not":
                    $x = new not;
                    break;
                case "if":
                    $x = new if_comp;
                    break;
                case "progn":
                    $x = new progn;
                    break;
                case "def":
                    $x = new def;
                    break;
                case "print":
                    $x = new printer;
                    break;
            }
        }
        $this->args[] = $x;
    }

    /**
     * Evaluate the current lisp expression.
     *
     * @return mixed|null
     */
    public function ev()
    {
        $op = array_shift($this->args);
        if ($op instanceof Operator) {
            $op->setArgs($this->args);

            return $op->ev();
        }

        return null;
    }
}

/**
 * Class Operator holds argument that it will use.
 */
abstract class Operator
{
    /**
     * @var mixed `carry` for map reduce function inital value
     */
    protected $car;
    /**
     * @var array arguments for this operator
     */
    protected $args = [];

    /**
     * @param array $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }

    /**
     * Eager evaluate all arguments.
     *
     * @param      $a
     * @param null $b
     */
    public function _eval(&$a, &$b = null)
    {
        if ($a instanceof lisp) {
            $a = $a->ev();
        } elseif (is_string($a) && isset($GLOBALS[$a])) {
            $a = $GLOBALS[$a];
        }

        if ($b instanceof lisp) {
            $b = $b->ev();
        } elseif (is_string($b) && isset($GLOBALS[$b])) {
            $b = $GLOBALS[$b];
        }
    }

    /**
     * Reduce the function args to a single argument using the L `lambda` function for this operator.
     *
     * @return mixed
     */
    public function ev()
    {
        return array_reduce(
            $this->args, function ($car, $val) {
            //for functions requiring at least 2 parameter.
            if ($car === null) {
                return $val;
            }

            return call_user_func($this->getL(), $car, $val);
        }, $this->car
        );
    }

    /**
     * Must return a "lambda" function that can be used to apply a operation to elements.
     *
     * @return mixed
     */
    abstract function getL();
}

/***
 * Now some classes follow that create actual operations.
 * The names make it pretty clear.
 *
 * @ToDo: Refactor the `getL` method which is not needed everywhere. Split into own file.
 */
class multiplication extends Operator
{
    protected $car = 1;

    public function getL()
    {
        return function ($a, $b) {
            parent::_eval($a, $b);

            return $a * $b;
        };
    }
}

class minus extends Operator
{
    protected $car = 0;

    public function getL()
    {
        return function ($a, $b) {
            parent::_eval($a, $b);

            return $a - $b;
        };
    }
}

class division extends Operator
{
    protected $car = null;

    public function getL()
    {
        return function ($a, $b) {
            parent::_eval($a, $b);

            return $a / $b;
        };
    }
}

class plus extends Operator
{
    protected $car = 0;

    public function getL()
    {
        return function ($a, $b) {
            parent::_eval($a, $b);

            return $a + $b;
        };
    }
}

class gt extends Operator
{
    protected $car = null;

    public function getL()
    {
        return function ($a, $b) {
            parent::_eval($a, $b);

            return $a > $b;
        };
    }
}

class gte extends Operator
{
    protected $car = null;

    public function getL()
    {
        return function ($a, $b) {
            parent::_eval($a, $b);

            return $a >= $b;
        };
    }
}

class comp extends Operator
{
    protected $car = null;

    public function getL()
    {
        return function ($a, $b) {
            parent::_eval($a, $b);

            return $a == $b;
        };
    }
}

class not extends Operator
{
    public function getL()
    {
    }

    public function ev()
    {
        return !$this->args[0];
    }
}

class if_comp extends Operator
{
    protected $car = null;

    public function getL()
    {
    }

    public function ev()
    {
        if ($this->args[0]) {
            return $this->args[1];
        } elseif (isset($this->args[2])) {
            return $this->args[2];
        }

        return null;
    }
}

class progn extends Operator
{
    protected $car = null;

    public function getL()
    {
    }

    public function ev()
    {
        $res = null;
        foreach ($this->args as $arg) {
            if ($arg instanceof lisp) {
                $res = $arg->ev();
            } else {
                $res = $arg;
            }

        }

        return $res;
    }
}

/**
 * Class printer for printing stuff.
 * Currently only using echo.
 *
 * @ToDo: Include sprintf
 */
class printer extends Operator
{
    protected $car = null;

    public function getL()
    {
    }

    public function ev()
    {
        if (isset($GLOBALS[$this->args[0]])) {
            echo $GLOBALS[$this->args[0]];
        } else {
            echo $this->args[0];
        }
    }
}

/**
 * @ToDo: Lexical/dynamic scoping ?
 * Very dirty hack using currently $_GLOBAL as variable store.
 */
class def extends Operator
{
    public function getL()
    {
    }

    public function ev()
    {
        $GLOBALS[$this->args[0]] = $this->args[1];

        return $GLOBALS[$this->args[0]];
    }
}
