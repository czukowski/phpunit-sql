<?php declare(strict_types=1);

namespace Cz\PHPUnit\SQL;

use LogicException,
    PHPUnit\Framework\Constraint\Constraint;

/**
 * AssertTrait
 * 
 * @author   czukowski
 * @license  MIT License
 */
trait AssertTrait
{
    /**
     * Assert two SQL queries or two series of SQL queries are equal.
     * 
     * @param  mixed    $expected      string[] or string
     * @param  mixed    $actual        string[] or string
     * @param  string   $message
     * @param  float    $delta
     * @param  integer  $maxDepth
     * @param  boolean  $canonicalize
     * @param  boolean  $ignoreCase
     */
    public function assertEqualsSQLQueries(
        $expected,
        $actual,
        string $message = '',
        float $delta = 0.0,
        int $maxDepth = 10,
        bool $canonicalize = FALSE,
        bool $ignoreCase = FALSE
    ): void
    {
        $flatten = function (array $value) {
            $flattened = [];
            array_walk_recursive(
                $value,
                function ($a) use ( & $flattened) {
                    $flattened[] = $a;
                }
            );
            return $flattened;
        };

        $expectedArray = $flatten([$expected]);
        $actualArray = $flatten([$actual]);
        $constraint = new EqualsSQLQueriesConstraint($expectedArray, $delta, $maxDepth, $canonicalize, $ignoreCase);
        $this->assertThat($actualArray, $constraint, $message);
    }

    /**
     * Asserts the executed SQL equal the expected values.
     * Works only if `getDatabaseDriver` method is implemented.
     * 
     * @param  mixed    $expected      string[] or string
     * @param  string   $message
     * @param  float    $delta
     * @param  integer  $maxDepth
     * @param  boolean  $canonicalize
     * @param  boolean  $ignoreCase
     */
    public function assertExecutedSQLQueries(
        $expected,
        string $message = '',
        float $delta = 0.0,
        int $maxDepth = 10,
        bool $canonicalize = FALSE,
        bool $ignoreCase = FALSE
    ): void
    {
        $actual = $this->getDatabaseDriver()
            ->getExecutedQueries();
        $this->assertEqualsSQLQueries($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    /**
     * @return  DatabaseDriverInterface
     * @throws  LogicException
     */
    protected function getDatabaseDriver(): DatabaseDriverInterface
    {
        // Override this method to return a `DatabaseDriverInterface` instance.
        throw new LogicException('Missing implementation');
    }

    /**
     * @param   string  $filename
     * @return  array
     */
    protected function loadSQLQueries(string $filename): array
    {
        return FileLoader::loadSQLFile($this->getLoadFilePath($filename));
    }

    /**
     * @param   string  $filename
     * @return  string
     */
    protected function getLoadFilePath(string $filename): string
    {
        return FileLoader::getFilePathFromObjectSubdirectory($this, $filename);
    }

    /**
     * @param  mixed       $value
     * @param  Constraint  $constraint
     * @param  string      $message
     */
    abstract public function assertThat($value, Constraint $constraint, string $message = ''): void;
}
