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

use District5\MinimumVersion\Exception\InvalidInitializationException;
use District5\MinimumVersion\Exception\MinimumVersionException;
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
     * Constructs a new instance of the Slim4Middleware class from environment variables.
     *
     * @param string $minimumVersionEnvKey - a key referencing the minimum version required
     * @param string|null $allowedVersionsEnvKey [optional] - a key referencing a comma separated list of allowed versions
     * @param string $headerName [optional] - the header name to use for the version, default is 'X-Version'
     * @return self
     * @throws InvalidInitializationException
     */
    public static function fromEnvironment(string $minimumVersionEnvKey, string|null $allowedVersionsEnvKey = null, string $headerName = self::VERSION_HEADER): self
    {
        $minVersion = getenv($minimumVersionEnvKey);
        if ($minVersion === false || empty(trim($minVersion))) {
            throw new InvalidInitializationException(
                sprintf(
                    'Environment variable %s not found',
                    $minimumVersionEnvKey
                )
            );
        }
        $minVersion = trim($minVersion);
        $allowedVersionsList = [];
        if ($allowedVersionsEnvKey !== null) {
            $allowedVersions = getenv($allowedVersionsEnvKey);
            if ($allowedVersions === false) {
                $allowedVersions = '';
            }
            $allowedVersionsList = explode(',', trim($allowedVersions));
            $allowedVersionsList = array_map('trim', $allowedVersionsList);
        }

        return new self($minVersion, $allowedVersionsList, $headerName);
    }

    /**
     * @param string $minimumVersion - the minimum version required, e.g. '1.0.0'
     * @param array $allowedVersions - a list of allowed versions, e.g. ['1.0.0', '1.0.1'] etc
     * @param string $headerName [optional] - the header name to use for the version, default is 'X-Version'
     * @return Slim4Middleware
     * @throws InvalidInitializationException
     */
    public static function fromValues(string $minimumVersion, array $allowedVersions = [], string $headerName = self::VERSION_HEADER): self
    {
        if (empty($minimumVersion)) {
            throw new InvalidInitializationException(
                'Minimum version cannot be empty'
            );
        }

        # version can be x.x.x.x, x.x.x, x.x, or x
        $minimumVersion = trim($minimumVersion);
        if (empty($minimumVersion)) {
            throw new InvalidInitializationException(
                'Minimum version cannot be empty'
            );
        }
        return new self($minimumVersion, $allowedVersions, $headerName);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $version = $request->getHeaderLine($this->versionHeader);
        if (empty($version)) {
            throw new HttpNotAcceptableException(
                $request,
                sprintf(
                    'Header %s is missing from the request',
                    $this->versionHeader
                )
            );
        }
        $version = trim($version);
        try {
            self::isValidVersion($version);
        } catch (MinimumVersionException) {
            throw new HttpNotAcceptableException(
                $request,
                sprintf(
                    'Version %s is not a valid representation of a version',
                    $version
                )
            );
        }

        if (in_array($version, $this->allowedVersions)) {
            return $handler->handle($request);
        }

        if (self::isVersionAtLeast($this->minimumVersion, $version) === false) {
            throw new HttpNotAcceptableException(
                $request,
                sprintf(
                    'Version %s is less than the minimum required version %s',
                    $version,
                    $this->minimumVersion
                )
            );
        }

        return $handler->handle($request);
    }
}
