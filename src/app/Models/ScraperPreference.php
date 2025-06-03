<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ScraperPreference extends Model
{
    protected $fillable = ['table_name', 'description', 'user_id', 'name'];

    public function get_table_definition(): string
    {
        if (empty($this->table_name)) {
            return "table_name is not set.";
        }

        $tableName = $this->table_name;
        
        if (!Schema::hasTable($tableName)) {
            return "Table '$tableName' does not exist.";
        }

        $columns = Schema::getColumnListing($tableName);
        $definition = "Table: $tableName\n\n";

        foreach ($columns as $column) {
            $type = Schema::getColumnType($tableName, $column);
            $definition .= "$column: $type\n";
        }

        return $definition;
    }
}
