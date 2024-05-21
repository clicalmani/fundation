<?php
namespace Clicalmani\Fundation\Logic\Internal;

use Clicalmani\Database\DB;
use Clicalmani\Fundation\Misc\RecursiveFilter;
use Clicalmani\XPower\XDTNodeList;

class Tonka
{
    /**
     * Migrated nodes
     * 
     * @var \Clicalmani\XPower\XDTNodeList[]
     */
    private $migrated = [];

    /**
     * Droped nodes
     * 
     * @var \Clicalmani\XPower\XDTNodeList[]
     */
    private $droped = [];

    /**
     * Console output object
     * 
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * Dump file name
     * 
     * @var ?string
     */
    private ?string $dump_file = NULL;
    
    /**
     * Migrate each node in the provided file.
     * 
     * @param string $filename
     * @return void
     */
    public function migrate(string $filename) : void
    {
        $this->maybeCreate($filename);

        $xdt = xdt();
        $xdt->setDirectory(database_path('/migrations'));
        $xdt->connect($filename, true, true);

        /** @var \Clicalmani\XPower\XDTNodeList[] */
        $nodes = [];

        foreach ($xdt->getDocumentRootElement()->children() as $node) {
            $node = $xdt->parse($node);
            $nodes[] = $node;
        }

        /** @var \Clicalmani\XPower\XDTNodeList[] */
        $skiped = $this->migrateProcess($nodes);
        
        while ( count($skiped) ) $skiped = $this->migrateProcess($skiped);

        $xdt->close();
        unset($skiped);
        $this->migrated = [];
    }

    /**
     * Drop all table from the current database.
     * 
     * @param string $filename Migration file
     * @return void
     */
    public function clearDB(string $filename) : void
    {
        $this->maybeCreate($filename);

        $xdt = xdt();
        $xdt->setDirectory(database_path('/migrations'));
        $xdt->connect($filename, true, true);

        /** @var \Clicalmani\XPower\XDTNodeList[] */
        $nodes = [];

        foreach ($xdt->getDocumentRootElement()->children() as $node) {
            $node = $xdt->parse($node);
            $nodes[] = $node;
        }

        $skiped = $this->dropProcess($nodes);
        
        while ( count($skiped) ) $skiped = $this->dropProcess($skiped);

        $xdt->close();
        unset($skiped);
        $this->droped = [];
    }

    /**
     * Make a fresh database migration.
     * 
     * @param string $filename
     * @param ?bool $seed Run database seeders
     * @param ?bool $execute_routines Execute database routines
     * @return void
     */
    public function migrateFresh(string $filename, ?bool $seed = true, ?bool $execute_routines = true) : void
    {
        $this->clearDB($filename);
        $this->migrate($filename);

        if (TRUE === $seed) $this->seed();

        if (TRUE === $execute_routines) {
            $this->routineFunctions();
            $this->routineProcs();
            $this->routineViews();
        }
    }

    /**
     * Export database migration
     * 
     * @param string $filename File to export to
     * @return void
     */
    public function exportSQL(string $filename) : void
    {
        $this->setOutput(NULL);
        $this->setDumpFile($filename);
        $this->migrate( time() );
    }

    /**
     * Output setter
     * 
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function setOutput(\Symfony\Component\Console\Output\OutputInterface|null $output) : void
    {
        $this->output = $output;
    }

    /**
     * Dump file setter
     * 
     * @param ?string $filename File name
     * @return void
     */
    public function setDumpFile(?string $filename = NULL) : void
    {
        $this->dump_file = $filename;
    }

    /**
     * Seed the default database
     * 
     * @param ?string $class
     */
    public function seed(?string $class = null) : bool
    {
        if (NULL !== $class) {
            require_once database_path("/seeders/$class.php");

            $seeder = new $class;

            $this->writeln('Running ' . $class);

            if ( $this->runSeed($seeder) ) {
                $this->writeln('success');

                return true;
            }

            $this->writeln('failure');

            return false;
        }

        try {
            $seeders_dir = new \RecursiveDirectoryIterator( database_path('/seeders') );
            $filter = new RecursiveFilter($seeders_dir);
            $filter->setPattern("\\.php$");

            $this->writeln('Seeding the database');

            foreach (new \RecursiveIteratorIterator($filter) as $file) { 
                $pathname = $file->getPathname();

                if($file->isFile()) {
                    $filename = $file->getFileName();
                    $class = substr($filename, 0, strlen($filename) - 4); 
                    
                    if(is_readable($pathname)) {
                        require database_path("/seeders/$class.php");

                        $classNs = "\Database\Seeders\\$class";
                        $seeder = new $classNs;

                        $this->writeln('Running ' . $class);

                        if ( $this->runSeed($seeder) ) {
                            $this->writeln('success');
                        } else {
                            $this->writeln('failure');
                        }
                    }
                }
            }

            return true;

        } catch(\PDOException $e) {
            $this->writeln($e->getMessage(), false);
            return false;
        }
    }

    /**
     * Create a function routine.
     * 
     * @return bool
     */
    public function routineFunctions() : bool
    {
        try {
            $functions_dir = new \RecursiveDirectoryIterator( database_path('/routines/functions'));
            $filter = new RecursiveFilter($functions_dir);
            // $filter->setPattern("\\.php$");

            $this->writeln('Migration routine functions ...');

            foreach (new \RecursiveIteratorIterator($filter) as $file) { 
                $pathname = $file->getPathname();
                $filename = $file->getFileName();

                if($file->isFile()) {
                    if(is_readable($pathname)) {
                        $function = require $pathname;
                        $this->writeln("Creating $filename ...");
                        $this->dropRoutine($filename, 'FUNCTION');
                        
                        if (false == $this->create($function)) {
                            $this->writeln('Failure');
                        } else $this->writeln('success');
                    }
                }
            }

            return true;

        } catch(\PDOException $e) {
            $this->writeln($e->getMessage(), false);
            return false;
        }
    }

    /**
     * Create procedure routine
     * 
     * @return bool
     */
    public function routineProcs() : bool
    {
        try {
            $procedures_dir = new \RecursiveDirectoryIterator( database_path('/routines/procedures') );
            $filter = new RecursiveFilter($procedures_dir);
            $filter->setPattern("\\.php$");

            $this->writeln('Migration stored procedures ...');

            foreach (new \RecursiveIteratorIterator($filter) as $file) { 
                $pathname = $file->getPathname();
                $filename = $file->getFileName();

                if($file->isFile()) {
                    if(is_readable($pathname)) {
                        $function = require $pathname;
                        $this->writeln("Creating $filename ...");
                        $this->dropRoutine($filename, 'PROCEDURE');
                        
                        if (false == $this->create($function)) {
                            $this->writeln('Failure');
                        } else $this->writeln('success');
                    }
                }
            }

            return true;

        } catch(\PDOException $e) {
            $this->writeln($e->getMessage(), false);
            return false;
        }
    }

    /**
     * Create view routine
     * 
     * @return bool
     */
    public function routineViews() : bool
    {
        try {
            $views_dir = new \RecursiveDirectoryIterator( database_path('/routines/views') );
            $filter = new RecursiveFilter($views_dir);
            $filter->setPattern("\\.php$");

            $this->writeln('Migration routine views ...');

            foreach (new \RecursiveIteratorIterator($filter) as $file) { 
                $pathname = $file->getPathname();
                $filename = $file->getFileName();

                if($file->isFile()) {
                    if(is_readable($pathname)) {
                        $function = require $pathname;
                        $this->writeln("Creating $filename ...");
                        $this->dropRoutine($filename, 'VIEW');
                        
                        if (false == $this->create($function)) {
                            $this->writeln('Failure');
                        } else $this->writeln('success');
                    }
                }
            }

            return true;

        } catch(\PDOException $e) {
            $this->writeln($e->getMessage(), false);
            return false;
        }
    }

    /**
     * Create a symbolic link
     * 
     * @param string $target Target of the link
     * @param string $link Link name
     * @return bool
     */
    public function link(string $target, string $link) : bool
    {
        return symlink($target, $link);
    }

    /**
     * Generate migration file
     * 
     * @param string $filename File name
     * @return bool TRUE on success, FALSE otherwise.
     */
    private function genMigrationFile(string $filename) : bool
    {
        $models_path = app_path('/Models');
        $migrations_path = database_path('/migrations');

        $dir = new \RecursiveDirectoryIterator($models_path);
        $filter = new RecursiveFilter($dir);
        $filter->setPattern("\\.php$");

        $xdt = xdt();
        $xdt->setDirectory($migrations_path);
        $xdt->newFile("$filename.xml", '<migration></migration>');
        $xdt->connect($filename, true, true);

        $tables = [];

        /**
         * Walkthrough models
         * Keep a track of each model and its entity.
         */
        foreach (new \RecursiveIteratorIterator($filter) as $file) {
            $modelClass = "App\\" . substr($file->getPathname(), strlen( root_path() ) + 4);
            $modelClass = substr($modelClass, 0, strlen($modelClass) - 4);

            $model = new $modelClass;
            $entity = $model->getEntity();

            $tables[$model->getTable()] = $modelClass;
            $xdt->getDocumentRootElement()->append('<entity model="' . $modelClass . '">' . get_class($entity) . '</entity>');
        }

        /**
         * Establish relationship
         * Each entity must have its dependences migrated before migrating itself.
         */
        foreach ($xdt->select('entity') as $node) {
            $node = $xdt->parse($node);
            $modelClass = $node->attr('model');
            $model = new $modelClass;
            $entity = $model->getEntity();
            $entity->setModel($model);

            if ($attributes = (new \ReflectionClass($entity))->getAttributes(\Clicalmani\Database\Factory\Index::class)) {

                foreach ($attributes as $attribute) {

                    $instance = $attribute->newInstance();
                    $refs = $instance->references;

                    if ($table = @$refs['table']) {
                        if (false == $node->hasChildren('dependences')) {
                            $node->append('<dependences></dependences>');
                        }

                        $depModelClass = $tables[$table];

                        $node->children()->first()->append('<entity model="' . $depModelClass . '">' . get_class(( new $depModelClass )->getEntity()) . '</entity>');
                    }
                }
            }
        }

        return $xdt->close(); 
    }

    /**
     * Run the specified seeder
     * 
     * @param \Clicalmani\Database\Seeders\Seeder $seeder
     * @return bool TRUE on success, FALSE otherwise.
     */
    private function runSeed(\Clicalmani\Database\Seeders\Seeder $seeder) : bool
    {
        try {
            $seeder->run();
            return true;
        } catch(\PDOException $e) {
            $this->writeln($e->getMessage(), false);
            return false;
        }
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     * 
     * @param ?string $message
     * @param ?bool $format Format output
     * @return void
     */
    private function writeln(?string $message = '', ?bool $format = true) : void
    {
        if ($this->output) {
            $this->output->writeln($format ? $this->formatOutput($message): $message);
        } else {
            printf("%s", $format ? $this->formatOutput($message): $message);
            print("<br/>");
        }
    }

    /**
     * Run a CREATE FUNCTION routine.
     * 
     * @param callable $function
     * @return bool
     */
    private function create(callable $function) : bool
    {
        try {
            $sql = str_replace('%DB_TABLE_PREFIX%', $_ENV['DB_TABLE_PREFIX'], $function());
            DB::getInstance()->query($sql);
            return true;
        } catch(\PDOException $e) {
            $this->writeln($e->getMessage(), false);
            return false;
        }
    }

    /**
     * Drop the specified routine
     * 
     * @param string $name
     * @param ?string $type
     * @return void
     */
    private function dropRoutine(string $name, string $type = 'FUNCTION') : void
    {
        DB::getInstance()->query("DROP $type IF EXISTS `$name`");
    }

    /**
     * Migration process
     * 
     * @param \Clicalmani\XPower\XDTNodeList[] $nodes
     * @return \Clicalmani\XPower\XDTNodeList[]
     */
    private function migrateProcess(array $nodes) : array
    {
        /**
         * Start by non dependent and no reference.
         */
        foreach ($nodes as $node) {
            
            if ($node->hasChildren('dependences') || $this->getReferences($node)->length) continue;
            
            $this->execute($node);
        }

        $nodes = collection($nodes)->filter(fn(XDTNodeList $node) => !$this->isMigrated($node))->toArray();

        /**
         * Walk through nodes with references and none dependent.
         */
        foreach ($nodes as $node) {
            
            if ($node->hasChildren('dependences') || $this->getReferences($node)->length === 0) continue;
            
            $this->execute($node);
        }

        $nodes = collection($nodes)->filter(fn(XDTNodeList $node) => !$this->isMigrated($node))->toArray();

        /**
         * Walk through nodes with dependences
         */
        foreach ($nodes as $node) {

            if (FALSE == $node->hasChildren('dependences')) continue;
            
            $count = 0;
            $children = $node->find('entity');

            foreach ($children as $child) {
                if ($this->isMigrated(xdt()->parse($child))) {
                    $count++;
                    continue;
                }
            }
            
            if ($count == $children->length) $this->execute($node);
        }

        $nodes = collection($nodes)->filter(fn(XDTNodeList $node) => !$this->isMigrated($node))->toArray();

        /**
         * Walk through nodes with dependences and references
         */
        foreach ($nodes as $node) {

            $refs = $this->getReferences($node);

            if (FALSE == $node->hasChildren('dependences') || $refs->length === 0) continue;
            
            $this->execute($node);

            foreach ($refs as $ref) {
                $ref = xdt()->parse($ref);
                $this->execute($ref);
            }
        }
        
        return collection($nodes)->filter(fn(XDTNodeList $node) => !$this->isMigrated($node))->toArray();
    }

    /**
     * Verify if a node is migrated.
     * 
     * @param \Clicalmani\XPower\XDTNodeList $node
     * @return bool TRUE on success, FALSE otherwise.
     */
    private function isMigrated(XDTNodeList $node) : bool
    {
        /** @var \Clicalmani\Database\Factory\Models\Model */
        $modelClass = $node->attr('model');
        $model = new $modelClass;
        $table1 = $model->getTable();

        foreach ($this->migrated as $n) {
            /** @var \Clicalmani\Database\Factory\Models\Model */
            $modelClass = $n->attr('model');
            $model = new $modelClass;
            $table2 = $model->getTable();

            if ($table1 == $table2) return true;
        }

        return false;
    }

    /**
     * Execute a node
     * 
     * @param \Clicalmani\XPower\XDTNodeList $node
     * @param ?string $command
     * @return void
     */
    private function execute(XDTNodeList $node, ?string $command = 'migrate') : void
    {
        /** @var \Clicalmani\Database\Factory\Models\Model */
        $modelClass = $node->attr('model');
        $model = new $modelClass;
        $entity = $model->getEntity();
        $entity->setModel($model);

        $table = $model->getTable();
        $check = ( $command === 'migrated' ) ? $this->isMigrated($node): $this->isDroped($node);

        if (FALSE === $check) $this->writeln(( ($command === 'migrate') ? 'Migrating ': 'Dropping ' ) . env('DB_TABLE_PREFIX', '') . $table);

        try {

            if (FALSE === $check) {
                if (NULL === $this->dump_file) $entity->{$command}();
                else $entity->{$command}(false, $this->dump_file);

                $this->writeln('Success');
                
                if ( $command === 'migrate' ) $this->migrated[] = $node;
                else $this->droped[] = $node;
            }

        } catch (\PDOException $e) {

            /**
             * |------------------------------------------------------------------------
             * | SQL Error Codes
             * |------------------------------------------------------------------------
             * | 1217 Occurs when a user tries to modify or delete a table that is part
             * | of a foreign key relationship, without addressing the dependency first.
             * | 23000 Integraty constraint violation
             */
            if (in_array($e->getCode(), ['HY000', '1217', '23000'])) $this->writeln('Failed'); 
            else throw new \Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Format output
     * 
     * @param string $message
     * @return string
     */
    private function formatOutput(string $message) : string
    {
        return str_pad("$message ", 100, '-');
    }

    /**
     * Get a node references
     * 
     * @param \Clicalmani\XPower\XDTNodeList $node
     * @return \Clicalmani\XPower\XDTNodeList
     */
    private function getReferences(XDTNodeList $node) : XDTNodeList
    {
        if ($owner = $node[0]->ownerDocument AND $root = $owner->firstChild) {
            $root = xdt()->parse($root);

            if ($relations = $root->find('dependences > entity[model="' . $node->attr('model') . '"]')) return $relations;
        }

        return new XDTNodeList;
    }

    /**
     * Dropping process
     * 
     * @param \Clicalmani\XPower\XDTNodeList[] $nodes
     * @return \Clicalmani\XPower\XDTNodeList[]
     */
    private function dropProcess(array $nodes) : array
    {
        /**
         * Search for independent nodes
         * Node that has no dependency and not referenced by
         * any other node.
         */
        foreach ($nodes as $node) {
            
            if ($node->hasChildren('dependences')) continue;

            $refs = $this->getReferences($node);

            if ($refs->length === 0) $this->execute($node, 'drop');
        }

        $nodes = collection($nodes)->filter(fn(XDTNodeList $node) => !$this->isDroped($node))->toArray();

        /**
         * Search for dependent (node with dependences) nodes 
         * with no reference (which has not been referenced by any other node).
         */
        foreach ($nodes as $node) {
            
            if (FALSE == $node->hasChildren('dependences')) continue;
            
            if ($this->getReferences($node)->length === 0) $this->execute($node, 'drop');
        }

        $nodes = collection($nodes)->filter(fn(XDTNodeList $node) => !$this->isDroped($node))->toArray();

        /**
         * Search for nodes with no dependency but with reference.
         */
        foreach ($nodes as $node) {
            
            if ($node->hasChildren('dependences')) continue;

            if ($this->getReferences($node)->length > 0) $this->execute($node, 'drop');
        }

        $nodes = collection($nodes)->filter(fn(XDTNodeList $node) => !$this->isDroped($node))->toArray();

        /**
         * Search for nodes both with dependency and reference.
         */
        foreach ($nodes as $node) {

            $refs = $this->getReferences($node);
            
            if (FALSE == $node->hasChildren('dependences') || $refs->length === 0) continue;

            foreach ($refs as $ref) {
                $ref = xdt()->parse($ref);
                $this->execute($ref, 'drop');
            }
        }

        return collection($nodes)->filter(fn(XDTNodeList $node) => !$this->isDroped($node))->toArray();;
    }

    /**
     * Verify if a node is droped.
     * 
     * @param \Clicalmani\XPower\XDTNodeList $node
     * @return bool TRUE on success, FALSE otherwise.
     */
    private function isDroped(XDTNodeList $node) : bool
    {
        /** @var \Clicalmani\Database\Factory\Models\Model */
        $modelClass = $node->attr('model');
        $model = new $modelClass;
        $table1 = $model->getTable();

        foreach ($this->droped as $n) {
            /** @var \Clicalmani\Database\Factory\Models\Model */
            $modelClass = $n->attr('model');
            $model = new $modelClass;
            $table2 = $model->getTable();

            if ($table1 == $table2) return true;
        }

        return false;
    }

    /**
     * May be create migration file
     * 
     * @param string $filename
     * @return void
     */
    private function maybeCreate(string $filename) : void
    {
        /** @var string */
        $migrations_path = database_path('/migrations');
        if ( !file_exists("$migrations_path/$filename.xml") ) $this->genMigrationFile($filename);
    }
}
