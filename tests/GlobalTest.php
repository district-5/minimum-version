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

namespace District5\MinimumVersionTests;


use District5\MinimumVersion\Exception\GeneralException;
use District5\MinimumVersion\Slim\Slim4Middleware;
use District5\MinimumVersionTests\SlimTests\SlimTestAbstract;

/**
 * Class GlobalTest
 * @package District5\MinimumVersionTests
 */
class GlobalTest extends SlimTestAbstract
{
    public function testValidVersions()
    {
        $validVersions = [
            '1',
            '1.2',
            '1.2.3',
            '1.2.3.4',
        ];
        $invalidVersions = [
            'a',
            '1.a',
            '1.2.a',
            '1.2.3.a',
        ];
        foreach ($validVersions as $version) {
            $this->assertTrue(Slim4Middleware::isValidVersion($version));
        }
    }

    public function testInvalidVersionOnePiece()
    {
        $this->expectException(GeneralException::class);
        Slim4Middleware::isValidVersion('a');
    }

    public function testValidVersionOnePiece()
    {
        $this->assertTrue(Slim4Middleware::isValidVersion('1'));
    }

    public function testGetComponentsOne()
    {
        $this->assertArrayHasKey('major', Slim4Middleware::getVersionComponents('1'));
    }

    public function testInvalidVersionTwoPieces()
    {
        $this->expectException(GeneralException::class);
        Slim4Middleware::isValidVersion('1.a');
    }

    public function testValidVersionTwoPieces()
    {
        $this->assertTrue(Slim4Middleware::isValidVersion('1.2'));
    }

    public function testGetComponentsTwo()
    {
        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys(
            ['major' => 1, 'minor' => 2],
            Slim4Middleware::getVersionComponents('1.2'),
            ['major', 'minor']
        );
    }

    public function testInvalidVersionThreePieces()
    {
        $this->expectException(GeneralException::class);
        Slim4Middleware::isValidVersion('1.2.a');
    }

    public function testValidVersionThreePieces()
    {
        $this->assertTrue(Slim4Middleware::isValidVersion('1.2.3'));
    }

    public function testGetComponentsThree()
    {
        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys(
            ['major' => 1, 'minor' => 2, 'patch' => 3],
            Slim4Middleware::getVersionComponents('1.2.3'),
            ['major', 'minor', 'patch']
        );
    }

    public function testInvalidVersionFourPieces()
    {
        $this->expectException(GeneralException::class);
        Slim4Middleware::isValidVersion('1.2.3.a');
    }

    public function testValidVersionFourPieces()
    {
        $this->assertTrue(Slim4Middleware::isValidVersion('1.2.3.4'));
    }

    public function testGetComponentsFour()
    {
        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys(
            ['major' => 1, 'minor' => 2, 'patch' => 3, 'build' => 4],
            Slim4Middleware::getVersionComponents('1.2.3.4'),
            ['major', 'minor', 'patch', 'build']
        );
    }

    public function testInvalidVersionFivePieces()
    {
        $this->expectException(GeneralException::class);
        Slim4Middleware::isValidVersion('1.2.3.4.a');
    }

    public function testValidVersionFivePieces()
    {
        $this->assertTrue(Slim4Middleware::isValidVersion('1.2.3.4.5'));
    }

    public function testGetComponentsFive()
    {
        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys(
            ['major' => 1, 'minor' => 2, 'patch' => 3, 'build' => 4, 'unmapped-0' => 5],
            Slim4Middleware::getVersionComponents('1.2.3.4.5'),
            ['major', 'minor', 'patch', 'build', 'unmapped-0']
        );
    }

    public function testInvalidVersionSixPieces()
    {
        $this->expectException(GeneralException::class);
        Slim4Middleware::isValidVersion('1.2.3.4.5.a');
    }

    public function testValidVersionSixPieces()
    {
        $this->assertTrue(Slim4Middleware::isValidVersion('1.2.3.4.5.6'));
    }

    public function testGetComponentsSix()
    {
        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys(
            ['major' => 1, 'minor' => 2, 'patch' => 3, 'build' => 4, 'unmapped-0' => 5, 'unmapped-1' => 6],
            Slim4Middleware::getVersionComponents('1.2.3.4.5.6'),
            ['major', 'minor', 'patch', 'build', 'unmapped-0', 'unmapped-1']
        );
    }

    public function testIsVersionAtLeastViaReflection()
    {
        $reflectionClass = new \ReflectionClass(Slim4Middleware::class);
        $reflectionMethod = $reflectionClass->getMethod('isVersionAtLeast');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $reflectionMethod->setAccessible(true);
        $this->assertTrue(
            $reflectionMethod->invokeArgs(
                null,
                ['1', '1']
            )
        );
        $this->assertTrue(
            $reflectionMethod->invokeArgs(
                null,
                ['1', '2']
            )
        );
        $this->assertTrue(
            $reflectionMethod->invokeArgs(
                null,
                ['1.2', '1.2']
            )
        );
        $this->assertTrue(
            $reflectionMethod->invokeArgs(
                null,
                ['1.2', '1.3']
            )
        );
        $this->assertTrue(
            $reflectionMethod->invokeArgs(
                null,
                ['1.2.3', '1.2.3']
            )
        );
        $this->assertTrue(
            $reflectionMethod->invokeArgs(
                null,
                ['1.2.3', '1.2.4']
            )
        );

        $this->assertFalse(
            $reflectionMethod->invokeArgs(
                null,
                ['1.2.3', '1.2.2']
            )
        );
        $this->assertFalse(
            $reflectionMethod->invokeArgs(
                null,
                ['1.2.3', '1.1.4']
            )
        );
        $this->assertFalse(
            $reflectionMethod->invokeArgs(
                null,
                ['1.2.3', '0.2.4']
            )
        );
        $this->assertFalse(
            $reflectionMethod->invokeArgs(
                null,
                ['1.2.3', '0.0.4']
            )
        );
    }
}
