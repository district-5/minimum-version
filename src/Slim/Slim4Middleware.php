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

namespace District5\MinimumVersion\Slim;

use District5\MinimumVersion\MiddlewareAbstract;
use District5\MinimumVersion\Slim\Exception\HttpNotAcceptableException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Slim4Middleware
 * @package District5\MinimumVersion\Slim
 */
class Slim4Middleware extends MiddlewareAbstract implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->checker->check($request->getHeaderLine($this->checker->getHeaderKey())) === false) {
            throw new HttpNotAcceptableException(
                $request,
                $this->checker->getLastError()
            );
        }

        return $handler->handle($request);
    }
}
