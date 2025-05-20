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

namespace District5\MinimumVersionTests\SlimTests;


use District5\MinimumVersion\Exception\InvalidInitializationException;
use District5\MinimumVersion\Slim\Exception\HttpNotAcceptableException;
use District5\MinimumVersion\Slim\Slim4Checker;
use District5\MinimumVersion\Slim\Slim4Middleware;
use District5\MinimumVersionTests\SlimTests\TestObjects\RequestHandlerTestObject;
use Fig\Http\Message\StatusCodeInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

/**
 * Class Slim4MiddlewareTest
 * @package District5\MinimumVersionTests\SlimTests
 */
class Slim4MiddlewareTest extends SlimTestAbstract
{
    public function testFromStringsInvalidMinimumVersion()
    {
        $this->expectException(InvalidInitializationException::class);
        $this->expectExceptionMessage('Minimum version x is not valid');

        Slim4Checker::fromValues('x', [], 'X-Version');
    }

    public function testFromStringsEmptyMinimumVersion()
    {
        $this->expectException(InvalidInitializationException::class);
        $this->expectExceptionMessage('Minimum version cannot be empty');

        Slim4Checker::fromValues('', [], 'X-Version');
    }

    public function testFromStringsEmptySpacedMinimumVersion()
    {
        $this->expectException(InvalidInitializationException::class);
        $this->expectExceptionMessage('Minimum version cannot be empty');

        Slim4Checker::fromValues(' ', [], 'X-Version');
    }

    public function testFromStringsInvalidHeaderName()
    {
        $this->expectException(InvalidInitializationException::class);
        $this->expectExceptionMessage('Header name cannot be empty');

        Slim4Checker::fromValues('1.0.0', [], '');
    }

    public function testFromEnvInvalidIgnoredMinimumVersion()
    {
        $this->expectException(InvalidInitializationException::class);
        $this->expectExceptionMessage('Minimum version some-invalid is not valid');

        Slim4Checker::fromEnvironment(
            'MIN_VERSION_INVALID',
            'ALLOWED_VERSIONS',
            'X-Version'
        );
    }

    /**
     * @return void
     * @throws InvalidInitializationException
     */
    public function testFromEnvWithoutAllowedVersionsDoesNotThrow()
    {
        $this->expectNotToPerformAssertions();
        Slim4Checker::fromEnvironment(
            'MIN_VERSION_VALID',
            'ALLOWED_VERSIONS_NO_SUCH_KEY', // key does not exist
            'X-Version'
        );
    }

    public function testFromEnvInvalidMinVersionKey()
    {
        $this->expectException(InvalidInitializationException::class);
        $this->expectExceptionMessageMatches('/Environment variable( THIS_IS_NOT_A_VALID_KEY)? not found/');

        Slim4Checker::fromEnvironment(
            'THIS_IS_NOT_A_VALID_KEY',
            'ALLOWED_VERSIONS',
            'X-Version'
        );
    }

    /**
     * @return void
     * @throws InvalidInitializationException
     */
    public function testNoVersionPasses()
    {
        $request = $this->generateRequest();

        $this->expectException(HttpNotAcceptableException::class);
        $this->expectExceptionMessageMatches('/Header X-Missing-Version is missing from the request/');

        $minVersion = '1.0.0';
        $allowedVersions = ['1.0.0', '1.0.1'];

        $instance = Slim4Checker::fromValues(
            $minVersion,
            $allowedVersions,
            'X-Missing-Version'
        );
        $middleware = new Slim4Middleware($instance);
        $middleware->process($request, new RequestHandlerTestObject());
    }

    /**
     * @return void
     * @throws InvalidInitializationException
     */
    public function testInvalidVersionHeaderValueThrows()
    {
        $request = $this->generateRequest();

        $this->expectException(HttpNotAcceptableException::class);
        $this->expectExceptionMessageMatches('/ is not a valid representation of a version/');

        $minVersion = '1.0.0';
        $allowedVersions = ['1.0.0', '1.0.1'];

        $instance = Slim4Checker::fromValues(
            $minVersion,
            $allowedVersions,
            'X-Foo'
        );
        $middleware = new Slim4Middleware($instance);
        $middleware->process($request, new RequestHandlerTestObject());
    }

    /**
     * @return void
     * @throws InvalidInitializationException
     */
    public function testInvalidVersionWithStaticValuesThrows()
    {
        $request = $this->generateRequest();

        $this->expectException(HttpNotAcceptableException::class);
        $this->expectExceptionMessageMatches('/0.0.9 is less than the minimum required version/');

        $minVersion = '1.0.0';
        $allowedVersions = ['1.0.0', '1.0.1'];

        $instance = Slim4Checker::fromValues(
            $minVersion,
            $allowedVersions,
            'X-Version'
        );
        $middleware = new Slim4Middleware($instance);
        $middleware->process($request, new RequestHandlerTestObject());
    }

    /**
     * @return void
     * @throws InvalidInitializationException
     */
    public function testInvalidVersionWithEnvironmentVariablesThrows()
    {
        $request = $this->generateRequest();

        $this->expectException(HttpNotAcceptableException::class);
        $this->expectExceptionMessageMatches('/0.0.9 is less than the minimum required version/');

        $instance = Slim4Checker::fromEnvironment(
            'MIN_VERSION_VALID',
            'ALLOWED_VERSIONS',
            'X-Version'
        );
        $middleware = new Slim4Middleware($instance);
        $middleware->process($request, new RequestHandlerTestObject());
    }

    /**
     * @return void
     * @throws InvalidInitializationException
     */
    public function testInvalidMinimumVersionButAllowedInListPasses()
    {
        $request = $this->generateRequest('0.0.8');

        $instance = Slim4Checker::fromValues(
            '1.0.0',
            ['0.0.8'], // despite requiring 1.0.0 as minimum, this is explicitly allowed
            'X-Version'
        );
        $middleware = new Slim4Middleware($instance);
        $result = $middleware->process($request, new RequestHandlerTestObject());
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $result->getStatusCode());
    }

    /**
     * @return void
     * @throws InvalidInitializationException
     */
    public function testValidMinimumContinues()
    {
        $request = $this->generateRequest('1.0.0');

        $instance = Slim4Checker::fromValues(
            '1.0.0',
            [],
            'X-Version'
        );
        $middleware = new Slim4Middleware($instance);
        $result = $middleware->process($request, new RequestHandlerTestObject());
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $result->getStatusCode());
    }

    /**
     * @param string $version
     * @return Request
     */
    private function generateRequest(string $version = '0.0.9'): Request
    {
        $fakeRequestBody = fopen('php://temp', 'r+');
        fwrite($fakeRequestBody, 'foo=bar&baz=qux');
        fseek($fakeRequestBody, 0);

        return new Request(
            'POST',
            new Uri('https', 'example.com', 443, '/test'),
            new Headers([
                'X-Foo' => ['Bar'],
                'X-Version' => [$version],
            ]),
            ['IS_A_COOKIE' => 'NOT REALLY'],
            ['IS_A_SERVER' => 'NOT REALLY'],
            new Stream($fakeRequestBody),
        );
    }
}
