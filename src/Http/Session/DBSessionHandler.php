<?php
namespace Clicalmani\Fundation\Http\Session;

use Clicalmani\Database\DB;

/**
 * @see https://stackoverflow.com/questions/36753513/how-do-i-save-php-session-data-to-a-database-instead-of-in-the-file-system
 */
class DBSessionHandler extends SessionHandler
{
    private $query;

    public function __construct()
    {
        $this->query = DB::table('sessions');
    }

    public function open(string $path, string $id) : bool
    {
        return $this->query->where('id = :id', ['id' => $id])->get()->count();
    }

    public function read(string $id): string|false
    {
        return $this->query->where('id = :id', ['id' => $id])->get()->first()['data'] ?? false;
    }

    public function write(string $id, string $data): bool
    {
        return $this->query->insert([
            [$id, time(), $data]
        ], true);
    }

    public function destroy(string $id): bool
    {
        return $this->query->where('id = :id', ['id' => $id])->delete()->exec()->status() === 'success';
    }

    public function close(): bool
    {
        $this->query->close();
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return $this->query->where('access < :old', ['old' => time() - $max_lifetime])->delete()->exec()->status() === 'success';
    }

    public function __destruct()
    {
        $this->close();
    }
}
