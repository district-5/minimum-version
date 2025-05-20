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

use District5\MinimumVersion\Exception\GeneralException;
use District5\MinimumVersion\Exception\InvalidInitializationException;
use District5\MinimumVersion\Exception\MinimumVersionException;

/**
 * Class MiddlewareAbstract
 * @package District5\MinimumVersion
 */
abstract class CheckerAbstract
{
    public const VERSION_HEADER = 'X-Version';

    /**
     * @var string
     */
    protected string $versionHeader = 'X-Version';

    /**
     * @var string
     */
    protected string $minimumVersion;

    /**
     * @var array
     */
    protected array $allowedVersions = [];

    /**
     * @var string
     */
    protected string $lastError;

    /**
     * MiddlewareAbstract constructor.
     *
     * @param string $minimumVersion - the minimum version required, e.g. '1.0.0'
     * @param array $allowedVersions - a list of allowed versions, e.g. ['1.0.0', '1.0.1'] etc
     * @param string $headerName [optional] - the header name to use for the version, default is 'X-Version'
     * @throws InvalidInitializationException
     */
    protected function __construct(string $minimumVersion, array $allowedVersions, string $headerName = self::VERSION_HEADER)
    {
        if (empty($headerName)) {
            throw new InvalidInitializationException(
                'Header name cannot be empty'
            );
        }

        $this->versionHeader = $headerName;
        $this->minimumVersion = $minimumVersion;
        try {
            self::isValidVersion($minimumVersion);
        } catch (MinimumVersionException) {
            throw new InvalidInitializationException(
                sprintf(
                    'Minimum version %s is not valid',
                    $minimumVersion
                )
            );
        }

        $this->allowedVersions = $allowedVersions;
    }

    /**
     * @param string $version
     * @param string $separator
     * @return bool
     * @throws MinimumVersionException
     */
    public static function isValidVersion(string $version, string $separator = '.'): bool
    {
        $components = self::getVersionComponents($version, $separator);
        $version = implode('.', array_map('strval', $components));

        return preg_match('/^\d+(\.\d+)*$/', $version) === 1;
    }

    /**
     * @param string $version
     * @param string $separator
     * @return array
     * @throws MinimumVersionException
     */
    public static function getVersionComponents(string $version, string $separator = '.'): array
    {
        $pieces = explode($separator, $version);
        $final = [];
        $namedComponents = ['major', 'minor', 'patch', 'build'];
        $unmapped = 0;
        foreach ($pieces as $i => $v) {
            if (!is_numeric($v)) {
                throw new GeneralException(
                    'Invalid version component at position ' . $i . ': "' . $v . '" in version "' . $version . '"'
                );
            }
            if (isset($namedComponents[$i])) {
                $final[$namedComponents[$i]] = (int)$v;
            } else {
                $final['unmapped-' . $unmapped] = (int)$v;
                $unmapped++;
            }
        }

        return $final;
    }

    /**
     * @param $lowestVersion
     * @param $providedVersion
     * @return bool
     */
    protected static function isVersionAtLeast($lowestVersion, $providedVersion): bool
    {
        return version_compare($providedVersion, $lowestVersion, '>=');
    }

    /**
     * @return string|null
     */
    public function getLastError(): string|null
    {
        if (!isset($this->lastError)) {
            return null;
        }
        return $this->lastError;
    }

    /**
     * @return string
     */
    public function getMinimumVersion(): string
    {
        return $this->minimumVersion;
    }

    /**
     * @param string $versionHeader
     * @return bool
     */
    abstract public function check(string $versionHeader): bool;

    /**
     * @return string
     */
    public function getHeaderKey(): string
    {
        return $this->versionHeader;
    }

    /**
     * Constructs a new instance of the middleware class from environment variables.
     *
     * @param string $minimumVersionEnvKey - a key referencing the minimum version required
     * @param string|null $allowedVersionsEnvKey [optional] - a key referencing a comma separated list of allowed versions
     * @param string $headerName [optional] - the header name to use for the version, default is 'X-Version'
     * @return self
     */
    abstract public static function fromEnvironment(string $minimumVersionEnvKey, string|null $allowedVersionsEnvKey = null, string $headerName = self::VERSION_HEADER): self;

    /**
     * @param string $minimumVersion - the minimum version required, e.g. '1.0.0'
     * @param array $allowedVersions - a list of allowed versions, e.g. ['1.0.0', '1.0.1'] etc
     * @param string $headerName [optional] - the header name to use for the version, default is 'X-Version'
     * @return self
     */
    abstract public static function fromValues(string $minimumVersion, array $allowedVersions = [], string $headerName = self::VERSION_HEADER): self;
}
