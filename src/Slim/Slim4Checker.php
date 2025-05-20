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

use District5\MinimumVersion\CheckerAbstract;
use District5\MinimumVersion\Exception\InvalidInitializationException;
use District5\MinimumVersion\Exception\MinimumVersionException;

/**
 * Class Slim4Checker
 * @package District5\MinimumVersion\Slim
 */
class Slim4Checker extends CheckerAbstract
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
     * @return Slim4Checker
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
     * @param string $versionHeader
     * @return bool
     */
    public function check(string $versionHeader): bool
    {
        if (empty($versionHeader)) {
            $this->lastError = sprintf('Header %s is missing from the request', $this->versionHeader);
            return false;
        }
        $version = trim($versionHeader);
        try {
            self::isValidVersion($version);
        } catch (MinimumVersionException) {
            $this->lastError = sprintf(
                'Version %s is not a valid representation of a version',
                $version
            );
            return false;
        }

        if (in_array($version, $this->allowedVersions)) {
            return true;
        }

        if (self::isVersionAtLeast($this->minimumVersion, $version) === false) {
            $this->lastError = sprintf(
                'Version %s is less than the minimum required version %s',
                $version,
                $this->minimumVersion
            );
            return false;
        }

        return true;
    }
}
