<?php

namespace Mage4\RunSql\Test\Integration\Console;

use Mage4\RunSql\Console\RunSqlCommand;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RunSqlCommandTest extends TestCase
{
    private ?ObjectManagerInterface $objectManager;
    private ?CommandTester $command;
    private ?string $query;
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @test
     * @magentoDbIsolation enabled
     */
    public function can_run_select_records_statement()
    {
        $this->given_there_is_query('select * from store where code="admin"');
        $this->when_run_sql_command_execute($this->query);
        $this->then_sql_query_output_matches(['name' => 'Admin', 'store_id' => 0]);
    }

    /**
     * @test
     * @magentoDbIsolation enabled
     */
    public function can_run_force_select_records_statement()
    {
        $this->given_there_is_query('describe store');
        $this->when_run_sql_command_execute($this->query, true);
        $this->then_sql_query_output_matches(['col1' => 'code', 'col2' => 'name']);
    }

    /**
     * @test
     * @magentoDbIsolation enabled
     */
    public function can_run_insert_statement()
    {
        $this->given_there_is_query('insert into store (code, website_id, group_id, name, sort_order, is_active) VALUES ("test", 1, 1, "Test Store", 0, 0)');
        $this->when_run_sql_command_execute($this->query);
        $this->then_sql_query_output_matches(['res' => '1 rows affected.']);
    }

    private function given_there_is_query(string $query): void
    {
        $this->query = $query;
    }

    private function when_run_sql_command_execute(string $query, bool $force = false): void
    {
        $this->command = new CommandTester(new RunSqlCommand($this->objectManager->get(ResourceConnection::class)));
        $this->command->execute(['sql' => $query, '--force-fetch' => $force]);
    }

    private function then_sql_query_output_matches(array $expected): void
    {
        $this->command->assertCommandIsSuccessful();
        $output = $this->command->getDisplay();
        foreach ($expected as $exp) {
            $this->assertStringContainsString($exp, $output);
        }
    }
}
