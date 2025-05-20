<?php
/**
 * District5 - MinimumVersion
 *
 * @copyright District5
 *
 * @author District5 YEAR to present
 * @link https://www.district5.co.uk
 *
 * @license MIT License
 */

namespace District5\MinimumVersion;

/**
 * Class MiddlewareAbstract
 * @package District5\MinimumVersion
 */
abstract class MiddlewareAbstract
{
    /**
     * @var CheckerAbstract
     */
    protected CheckerAbstract $checker;

    /**
     * @param CheckerAbstract $checker
     */
    public function __construct(CheckerAbstract $checker)
    {
        $this->checker = $checker;
    }
}
