<?php
namespace Clicalmani\Fundation\Http\Session;

use Clicalmani\Database\DB;

/**
 * @see https://stackoverflow.com/questions/36753513/how-do-i-save-php-session-data-to-a-database-instead-of-in-the-file-system
 */
class DBSessionHandler extends SessionHandler
{
    private $pdo, $driver;

    public function __construct(bool $encrypt, ?array $flags = [])
    {
        parent::__construct($encrypt, $flags);
        $this->driver = $flags['driver'];

    }

    public function open(string $path, string $id) : bool
    {
        if ($this->pdo = DB::getConnection($this->driver)) return true;
        return false;
    }

    public function read(string $id): string|false
    {
        return $this->pdo->query("SELECT `data` FROM $this->table WHERE id = '$id'")->fetch(\PDO::FETCH_NUM)[0] ?? '';
    }

    public function write(string $id, string $data): bool
    {
        $access = time();
        return !!$this->pdo->query("REPLACE INTO $this->table (`id`, `access`, `data`) VALUES ('$id', '$access', '$data')");
    }

    public function destroy(string $id): bool
    {
        return !!$this->pdo->query("DELETE FROM $this->table WHERE id = '$id'");
    }

    public function close(): bool
    {
        $this->pdo = null;
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return $this->pdo->query("DELETE FROM $this->table WHERE `access` < " . (time() - $max_lifetime))->rowCount();
    }

    #[\ReturnTypeWillChange]
    public function create_sid()
    {
        return (string)session_create_id($this->getIdPrefix());
    }

    public function validate_sid(string $id)
    {
        return $this->pdo->query("SELECT `data` FROM $this->table WHERE id = '$id'")->rowCount();
    }

    public function __destruct()
    {
        $this->close();
    }
}
