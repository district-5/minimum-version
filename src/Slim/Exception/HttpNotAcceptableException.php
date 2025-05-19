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

namespace District5\MinimumVersion\Slim\Exception;

use Slim\Exception\HttpSpecializedException;

/**
 * Class HttpNotAcceptableException
 * @package District5\MinimumVersion\Slim\Exception
 */
class HttpNotAcceptableException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 406;

    /**
     * @var string
     */
    protected $message = 'Not Acceptable.';

    /**
     * @var string
     */
    protected string $title = '406 Not Acceptable';

    /**
     * @var string
     */
    protected string $description = 'The version requested is not acceptable. Please try again.';
}
